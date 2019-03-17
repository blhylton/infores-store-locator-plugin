<?php

namespace BLHylton\InfoResStoreLocator;

use GuzzleHttp\TransferStats;
use PHPHtmlParser\Dom;
use GuzzleHttp\Client;
use BLHylton\InfoResStoreLocator\Model\PageData;

/**
 * Class that deals with getting data from a given URL and parsing it under the assumption that it is an infores product
 * location page.
 * @author Barry Hylton <bhylton@stellarstudios.com>
 * @license MIT
 * @package BLHylton\InfoResStoreLocator
 */
class DataParser
{

    /**
     * @var $client Client
     */
    protected $client;
    /**
     * @var $url string
     */
    protected $url;

    /**
     * DataParser constructor.
     * @param \GuzzleHttp\Client $client Guzzle HTTP client to use with this parser
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }

    /**
     * Utilizes the Guzzle Client passed in in the constructor to get the HTML data from the page with the parameters
     * needed to locate the proper page on infores.
     *
     * @param string $clientId Necessary Parameter from URL
     * @param string $productFamilyId Necessary Parameter from URL
     * @param string $template Necessary Parameter from URL
     * @param string $productType Type of identifier to use
     * @param string $zipCode Zip Code to search from
     * @param string $productId Product Id to search for (with productType 'upc', this is the upc number)
     * @param int $distance Distance in miles to search, defaults to 20 if left null
     * @param int|null $page Will default to page 1 if left null
     * @return \Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getHTML($clientId, $productFamilyId, $template, $productType, $zipCode, $productId, $page = null, $distance = 20)
    {
        return $this->client->request('GET', '', [
            'query' => [
                'clientid' => $clientId,
                'productfamilyid' => $productFamilyId,
                'zip' => $zipCode,
                'productid' => $productId,
                'template' => $template,
                'producttype' => $productType,
                'searchradius' => $distance,
                'storespagenum' => $page ?? '1'
            ],
            'on_stats' => function (TransferStats $stats) {
                $this->setUrl($stats->getEffectiveUri());
            }
        ])->getBody()->getContents();

    }

    /**
     * Take html from getHTML (or anywhere really, as long as it has the same structure as an infores store locator) and
     * return a PageData object with the corresponding values.
     *
     * @param $HTMLString
     * @return object
     */
    public function parseDataFromHTML($HTMLString)
    {
        $snapshot = new PageData;
        $dom = new Dom;
        $dom->load($HTMLString);

        $headers = [];
        //Get data from table rows
        foreach ($dom->find('tr') as $rIdx => $row) {
            if ($rIdx === 0) {
                $headers = $this->createDataHeaders($row);
                continue;
            }

            $snapshot->data[] = $this->getDataFromRow($row, $headers);
        }

        $anchors = $dom->find('#stores > center > a');
        //Test for second page
        $snapshot->morePages = $this->probeAnchorsForNextPage($anchors);

        $snapshot->currentPageNumber = $this->getPageNumber();

        return (object)$snapshot;
    }

    /**
     * Parse row that contains headers for table
     * @param Dom\HtmlNode $rowNode
     * @return array
     */
    private function createDataHeaders(Dom\HtmlNode $rowNode)
    {
        $headers = [];
        foreach ($rowNode->find('td') as $cIdx => $cell) {
            $headers[$cIdx] = $this->cleanUpNodeData($cell->text(true));
        }
        return $headers;
    }

    /**
     * Clean up the nodes by stripping out the extra space and turning numeric strings into a float.
     * @param $node
     * @return float|string
     */
    private function cleanUpNodeData($node)
    {
        // Trim leading and trailing space, remove extra double (or more) spaces, and see if it's a number, in that order.
        return $this->attemptToCastToFloat(
            preg_replace('/\s+/', ' ',
                trim($node)
            )
        );
    }

    /**
     * Test if value is numeric, and if it is, cast it to a float
     * @param $str
     * @return float|string
     */
    private function attemptToCastToFloat($str)
    {
        if (is_numeric($str)) {
            return floatval($str);
        }

        return $str;
    }

    /**
     * Parse row that contains data
     * @param Dom\HtmlNode $rowNode
     * @param $headers
     * @return object
     */
    private function getDataFromRow(Dom\HtmlNode $rowNode, $headers)
    {
        $dataPoint = [];
        foreach ($rowNode->find('td') as $cIdx => $cell) {
            $header = $headers[$cIdx];

            // Discard the Map since its effectively useless and can easily be generated on the fly with better information
            if ($header === 'Map') {
                continue;
            }

            $dataPoint[$headers[$cIdx]] = $this->cleanUpNodeData($cell->text(true));
        }

        return (object)$dataPoint;
    }

    /**
     * Check anchors for any that say "More Stores" so that we can determine if we're on the last page of results.
     * @param Dom\Collection $anchorArray
     * @return bool
     */
    private function probeAnchorsForNextPage(Dom\Collection $anchorArray)
    {
        /** @var Dom\HtmlNode $anchor */
        foreach ($anchorArray as $anchor) {
            if ($anchor->text(true) === 'More Stores') {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse page number out of the URL that was set either manually or by the request object.
     * @return string
     */
    public function getPageNumber(): string
    {
        $parsedPageUrl = parse_url($this->url, PHP_URL_QUERY);
        $parsedPageUrlArray = [];
        foreach (explode('&', $parsedPageUrl) as $couple) {
            list ($key, $val) = explode('=', $couple);
            $parsedPageUrlArray[$key] = $val;
        }
        return $parsedPageUrlArray['storespagenum'];
    }
}