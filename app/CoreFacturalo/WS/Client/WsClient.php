<?php

namespace App\CoreFacturalo\WS\Client;

use SoapClient;

class WsClient
{
    private $client;

    /**
     * SoapClient constructor.
     *
     * @param string $wsdl       Url of WSDL
     * @param array  $parameters Soap's parameters
     */
    public function __construct($wsdl = '', $parameters = [])
    {
        if (empty($wsdl)) {
            $wsdl = __DIR__.DIRECTORY_SEPARATOR.'Resources'.
                            DIRECTORY_SEPARATOR.'wsdl'.
                            DIRECTORY_SEPARATOR.'billService.wsdl';
        }
        $this->client = new SoapClient($wsdl, $parameters);
    }

    /**
     * @param $user
     * @param $password
     */
    public function setCredentials($user, $password)
    {
        $header = new WsSecurityHeader($user, $password);
        $this->client->__setSoapHeaders($header->getHeader());
    }

    /**
     * Set Url of Service.
     *
     * @param string $url
     */
    public function setService($url)
    {
        $this->client->__setLocation($url);
    }

    /**
     * @param $function
     * @param $arguments
     *
     * @return mixed
     */
    public function call($function, $arguments)
    {
        return $this->client->__soapCall($function, $arguments);
    }
}
