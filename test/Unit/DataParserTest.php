<?php

namespace BLHylton\InfoResStoreLocator\Test\Unit;

use GuzzleHttp\Middleware;
use BLHylton\InfoResStoreLocator\DataParser;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class DataParserTest extends TestCase
{
    /**
     * @var DataParser
     */
    protected $parser;
    protected $guzzleMockRequestContainer = [];

    public function setUp(): void
    {
        $this->guzzleMockRequestContainer = [];

        $htmlDoc = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'testData' . DIRECTORY_SEPARATOR . 'external5.html');
        $history = Middleware::history($this->guzzleMockRequestContainer);
        $mock = new MockHandler([
            new Response(200, [], $htmlDoc)
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = new Client([
            'base_uri' => 'http://productlocator.infores.com/productlocator/servlet/ProductLocator',
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36',
                'Accept' => 'text/plain, text/html',
                'Origin' => 'http://productlocator.infores.com/'
            ],
            'timeout' => 5,
            //Test handler for mocking requests
            'handler' => $stack
        ]);

        $this->parser = new DataParser($client);
        /**
         * Normally, this would be set when we make a request, but this is required for some of the tests where we do
         * not make a full request, so it's set here to imitate what would be set on a proper request. We do test that
         * this is being set correctly when we test making a request.
         */
        $this->parser->setUrl("http://productlocator.infores.com/productlocator/servlet/ProductLocator" .
            "?clientid=173" .
            "&productfamilyid=JJSF" .
            "&zip=37604" .
            "&productid=5724304606" .
            "&template=keg_nl.xsl" .
            "&producttype='upc'" .
            "&searchradius=5" .
            "&storespagenum=1");
    }

    public function testGetHTML()
    {
        $uriParams = [
            'clientId' => '173',
            'productFamilyId' => 'JJSF',
            'zip' => 37604,
            'productId' => 5724304606,
            'template' => 'keg_nl.xsl',
            'productType' => 'upc'
        ];
        $html = $this->parser->getHTML(
            $uriParams['clientId'],
            $uriParams['productFamilyId'],
            $uriParams['template'],
            $uriParams['productType'],
            $uriParams['zip'],
            $uriParams['productId']
        );

        $htmlDoc = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'testData' . DIRECTORY_SEPARATOR . 'external5.html');
        $this->assertEquals($htmlDoc, $html);
    }

    public function testGetHtmlURL()
    {
        $uriParams = [
            'clientId' => '173',
            'productFamilyId' => 'JJSF',
            'zip' => 37604,
            'productId' => 5724304606,
            'template' => 'keg_nl.xsl',
            'productType' => 'upc'
        ];
        $this->parser->getHTML(
            $uriParams['clientId'],
            $uriParams['productFamilyId'],
            $uriParams['template'],
            $uriParams['productType'],
            $uriParams['zip'],
            $uriParams['productId']
        );

        $uri = $this->parser->getUrl();
        $testUri = "http://productlocator.infores.com/productlocator/servlet/ProductLocator" .
            "?clientid={$uriParams['clientId']}" .
            "&productfamilyid={$uriParams['productFamilyId']}" .
            "&zip={$uriParams['zip']}" .
            "&productid={$uriParams['productId']}" .
            "&template={$uriParams['template']}" .
            "&producttype={$uriParams['productType']}" .
            "&searchradius=20" .
            "&storespagenum=1";

        $this->assertEquals($testUri, $uri);
    }

    public function testGetHtmlWithDistance()
    {
        $uriParams = [
            'clientId' => '173',
            'productFamilyId' => 'JJSF',
            'zip' => 37604,
            'productId' => 5724304606,
            'template' => 'keg_nl.xsl',
            'productType' => 'upc',
            'distance' => 10
        ];
        $this->parser->getHTML(
            $uriParams['clientId'],
            $uriParams['productFamilyId'],
            $uriParams['template'],
            $uriParams['productType'],
            $uriParams['zip'],
            $uriParams['productId'],
            null,
            $uriParams['distance']
        );

        $uri = $this->parser->getUrl();
        $testUri = "http://productlocator.infores.com/productlocator/servlet/ProductLocator" .
            "?clientid={$uriParams['clientId']}" .
            "&productfamilyid={$uriParams['productFamilyId']}" .
            "&zip={$uriParams['zip']}" .
            "&productid={$uriParams['productId']}" .
            "&template={$uriParams['template']}" .
            "&producttype={$uriParams['productType']}" .
            "&searchradius={$uriParams['distance']}" .
            "&storespagenum=1";
        $this->assertEquals($testUri, $uri);
    }

    public function testGetHtmlWithPageNum()
    {
        $uriParams = [
            'clientId' => '173',
            'productFamilyId' => 'JJSF',
            'zip' => 37604,
            'productId' => 5724304606,
            'template' => 'keg_nl.xsl',
            'productType' => 'upc',
            'pageNum' => 2
        ];
        $this->parser->getHTML(
            $uriParams['clientId'],
            $uriParams['productFamilyId'],
            $uriParams['template'],
            $uriParams['productType'],
            $uriParams['zip'],
            $uriParams['productId'],
            $uriParams['pageNum'],
        );

        $uri = $this->parser->getUrl();
        $testUri = "http://productlocator.infores.com/productlocator/servlet/ProductLocator" .
            "?clientid={$uriParams['clientId']}" .
            "&productfamilyid={$uriParams['productFamilyId']}" .
            "&zip={$uriParams['zip']}" .
            "&productid={$uriParams['productId']}" .
            "&template={$uriParams['template']}" .
            "&producttype={$uriParams['productType']}" .
            "&searchradius=20" .
            "&storespagenum={$uriParams['pageNum']}";
        $this->assertEquals($testUri, $uri);
    }

    public function testGetHtmlWithDistanceAndPage()
    {
        $uriParams = [
            'clientId' => '173',
            'productFamilyId' => 'JJSF',
            'zip' => 37604,
            'productId' => 5724304606,
            'template' => 'keg_nl.xsl',
            'productType' => 'upc',
            'distance' => 30,
            'pageNumber' => 2
        ];
        $this->parser->getHTML(
            $uriParams['clientId'],
            $uriParams['productFamilyId'],
            $uriParams['template'],
            $uriParams['productType'],
            $uriParams['zip'],
            $uriParams['productId'],
            $uriParams['pageNumber'],
            $uriParams['distance']
        );

        $uri = $this->parser->getUrl();
        $testUri = "http://productlocator.infores.com/productlocator/servlet/ProductLocator" .
            "?clientid={$uriParams['clientId']}" .
            "&productfamilyid={$uriParams['productFamilyId']}" .
            "&zip={$uriParams['zip']}" .
            "&productid={$uriParams['productId']}" .
            "&template={$uriParams['template']}" .
            "&producttype={$uriParams['productType']}" .
            "&searchradius={$uriParams['distance']}" .
            "&storespagenum={$uriParams['pageNumber']}";
        $this->assertEquals($testUri, $uri);
    }

    public function testSinglePageParseDataFromHTML()
    {
        $htmlDoc = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'testData' . DIRECTORY_SEPARATOR . 'external5.html');
        $rawData = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'testData' . DIRECTORY_SEPARATOR . 'stores5.json');
        $decodedJsonData = json_decode($rawData);
        $dataFromHTML = $this->parser->parseDataFromHTML($htmlDoc);
        $this->assertSameSize($decodedJsonData, $dataFromHTML->data);

        foreach ($decodedJsonData as $idx => $datum) {
            $this->assertEquals($datum, $dataFromHTML->data[$idx]);
        }

        $this->assertFalse($dataFromHTML->morePages);
    }

    public function testMultiPageFirstPageParseDataFromHTML()
    {
        $htmlDoc = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'testData' . DIRECTORY_SEPARATOR . 'external20p1.html');
        $rawData = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'testData' . DIRECTORY_SEPARATOR . 'stores20.json');
        $decodedJsonData = array_slice(json_decode($rawData), 0, 10);
        $dataFromHTML = $this->parser->parseDataFromHTML($htmlDoc);
        $this->assertSameSize($decodedJsonData, $dataFromHTML->data);

        foreach ($decodedJsonData as $idx => $datum) {
            $this->assertEquals($datum, $dataFromHTML->data[$idx]);
        }

        $this->assertTrue($dataFromHTML->morePages);
    }

    public function testMultiPageLastPageParseDataFromHTML()
    {
        $htmlDoc = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'testData' . DIRECTORY_SEPARATOR . 'external20p2.html');
        $rawData = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'testData' . DIRECTORY_SEPARATOR . 'stores20.json');
        $decodedJsonData = array_slice(json_decode($rawData), 10);
        $dataFromHTML = $this->parser->parseDataFromHTML($htmlDoc);
        $this->assertSameSize($decodedJsonData, $dataFromHTML->data);

        foreach ($decodedJsonData as $idx => $datum) {
            $this->assertEquals($datum, $dataFromHTML->data[$idx]);
        }

        $this->assertFalse($dataFromHTML->morePages);
    }

    public function testGetPageNumber()
    {
        $testValues = range(1, 20);

        $resultValues = [];
        foreach ($testValues as $value) {
            $this->parser->setUrl("http://productlocator.infores.com/productlocator/servlet/ProductLocator" .
                "?clientid=173" .
                "&productfamilyid=JJSF" .
                "&zip=37604" .
                "&productid=5724304606" .
                "&template=keg_nl.xsl" .
                "&producttype='upc'" .
                "&searchradius=5" .
                "&storespagenum={$value}");

            $resultValues[] = $this->parser->getPageNumber();
        }

        $this->assertEquals($testValues, $resultValues);
    }
}
