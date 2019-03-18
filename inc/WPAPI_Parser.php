<?php

namespace BLHylton\InfoResStoreLocator\WordPress;

use BLHylton\InfoResStoreLocator\DataParser;
use GuzzleHttp\Client;

class WPAPI_Parser
{
    private $globalRequestParameters;

    public function __construct()
    {
        $this->globalRequestParameters = [
            'baseURI' => get_option('blhirsl-base-uri'),
            'clientID' => get_option('blhirsl-client-id'),
            'productFamilyID' => get_option('blhirsl-product-family-id'),
            'template' => get_option('blhirsl-template'),
            'productType' => get_option('blhirsl-product-type')
        ];
    }

    public function init()
    {
        add_action('rest_api_init', array($this, 'registerRoute'));
    }

    public function registerRoute()
    {
        register_rest_route('blhirsl/v1', '/get-stores', [
            'methods' => 'GET',
            'callback' => array($this, 'run'),
            'args' => [
                'zipCode' => [
                    'required' => true
                ],
                'productId' => [
                    'required' => true
                ],
                'distance' => [
                    'default' => 20
                ],
                'pageNum' => [
                    'default' => 1
                ]
            ]
        ]);
    }

    public function run(\WP_REST_Request $request)
    {
        $params = $request->get_params();
        $validation = $this->validateAPICall();
        if ($validation !== null) {
            return $validation;
        }
        $client = $this->configureClient();
        $parser = new DataParser($client);
        return $this->getData($parser, $params);
    }

    public function validateAPICall()
    {
        if (in_array(false, $this->globalRequestParameters)) {
            return new \WP_Error(
                'improper_configuration',
                'The plugin is missing some required configuration options.',
                array('status' => 422)
            );
        }

        return null;
    }

    public function configureClient()
    {
        $parsedUrl = parse_url($this->globalRequestParameters['baseURI']);

        return new Client([
            'base_uri' => $this->globalRequestParameters['baseURI'],
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36',
                'Accept' => 'text/plain, text/html',
                'Origin' => "{$parsedUrl['scheme']}://{$parsedUrl['host']}/"
            ],
            'timeout' => 5
        ]);
    }

    public function getData(DataParser $parser, $params)
    {
        try {
            $html = $parser->getHTML(
                $this->globalRequestParameters['clientID'],
                $this->globalRequestParameters['productFamilyID'],
                $this->globalRequestParameters['template'],
                $this->globalRequestParameters['productType'],
                $params['zipCode'],
                $params['productId'],
                $params['pageNum'],
                $params['distance']
            );
        } catch (\Exception $e) {
            return new \WP_Error('http_fail', 'HTTP request to InfoRes failed');
        }

        return $parser->parseDataFromHTML($html);
    }
}