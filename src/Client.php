<?php

namespace Cake;

use GuzzleHttp\Client as GuzzleClient;

class Client
{

    /**
     * Cake Affiliate Id
     *
     * @var string
     */
    protected $affiliateId;

    /**
     * Cake Api Key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Cake Domain
     *
     * @var string
     */
    protected $domain;

    /**
     * Currently only supports http (SOAP support to come)
     *
     * @var string
     */
    protected $driver = 'http';

    /**
     * Cake Api version
     *
     * @var string
     */
    protected $version = '2';

    /**
     * @var GuzzleClient
     */
    protected $apiClient;

    /**
     * Endpoint of api calls
     *
     * @var string get|account|offers|reports
     */
    protected $endpoint;

    /**
     * Verbs in endpoin functions that we send post requests to.
     * All other requests are get requests.
     *
     * @var array
     */
    protected static $postVerbs = ['set', 'send', 'add', 'apply', 'reset'];

    /**
     * Client constructor. ['affiliate_id' => AFFILIATE_ID, 'api_key' => API_KEY]
     * @param array $config
     */
    public function __construct($config = [])
    {
        if (isset($config['affiliate_id'])) {
            $this->affiliateId = $config['affiliate_id'];
        }

        if (isset($config['api_key'])) {
            $this->apiKey = $config['api_key'];
        }

        if (isset($config['domain'])) {
            $this->domain = $config['domain'];
        }

        if (isset($config['version'])) {
            $this->version = $config['version'];
        }
    }

    /**
     * @param $method
     * @param $args
     * @return Response
     */
    public function __call($method, $args)
    {
        if (!$this->apiClient) {
            $this->initApiClient();
        }

        $httpMethod = $this->getHttpMethod($method);
        $functionUri = $this->getFunctionUri($method);

        return $this->makeRequest($httpMethod, $functionUri, $args);
    }

    /**
     * @param $httpMethod
     * @param $functionUri
     * @param $args
     * @return Response
     */
    public function makeRequest($httpMethod, $functionUri, $args)
    {
        // Get Credentials and merge or just credentials if the call is empty
        $params = isset($args[0]) ? array_merge($args[0], $this->getCredentials()) : $this->getCredentials();
        // Set the correct key for the http method
        $paramKey = $httpMethod === 'post' ? 'form_params' : 'query';

        $response = $this->apiClient->request($httpMethod, $functionUri, [$paramKey => $params]);

        return (new Response($response));
    }

    /**
     * Get credential array
     *
     * @return array
     */
    protected function getCredentials()
    {
        return [
            'api_key'      => $this->apiKey,
            'affiliate_id' => $this->affiliateId
        ];
    }

    /**
     * Gets the fully qualified uri/api to call
     *
     * @param $method
     * @return string
     */
    protected function getFunctionUri($method)
    {
        return $this->endpoint . '/' . ucfirst($method);
    }

    /**
     * Returns Http method to use for call
     *
     * @param $calledMethod
     * @return string
     */
    protected function getHttpMethod($calledMethod)
    {
        return preg_match('/^(' . implode('|', self::$postVerbs) . ')/i', $calledMethod) ? 'post' : 'get';
    }

    /**
     * Set endpoint to get.
     *
     * @return $this
     */
    public function get()
    {
        return $this->endpoint('get');
    }

    /**
     * Set endpoint to accounts.
     *
     * @return $this
     */
    public function account()
    {
        return $this->endpoint('account');
    }

    /**
     * Set endpoint to offers.
     *
     * @return $this
     */
    public function offers()
    {
        return $this->endpoint('offers');
    }

    /**
     * Set endpoint to reports.
     *
     * @return $this
     */
    public function reports()
    {
        return $this->endpoint('reports');
    }

    /**
     * Set endpoint.
     *
     * @param null $endpoint
     * @return $this|string
     */
    public function endpoint($endpoint = null)
    {
        if (is_null($endpoint)) {
            return $this->endpoint;
        }

        $this->endpoint = $endpoint . '.asmx';

        return $this;
    }

    /**
     * Returns the base url for api calls.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return 'http://' . $this->domain . '/affiliates/api/' . $this->version . '/';
    }

    /**
     * Instantiate a new api client to perform calls on.
     */
    public function initApiClient()
    {
        $this->apiClient = new GuzzleClient([
            'base_uri' => $this->getBaseUrl()
        ]);
    }

}
