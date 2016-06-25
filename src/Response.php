<?php

namespace Cake;

use Psr\Http\Message\ResponseInterface;

class Response
{
    /**
     * @var ResponseInterface
     */
    protected $apiResponse;

    /**
     * @var string
     */
    protected $xml;

    /**
     * Response constructor.
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->apiResponse = $response;
        $this->xml = $this->xml();
    }
    
    public function __get($prop)
    {
        return $this->xml->{$prop};
    }

    /**
     * Magic method if the method isnt defined in this class then call it on the ResponseInterface
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->apiResponse->{$method}($args);
    }

    /**
     * @return \SimpleXMLElement|string
     */
    public function xml()
    {
        if($this->xml) {
            return $this->xml;
        }
        
        $xmlString = (string) $this->apiResponse->getBody();
        
        return simplexml_load_string($xmlString);
    }

    /**
     * Return the underlying response
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->apiResponse;
    }
}
