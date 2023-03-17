<?php

namespace Laminas\OAuth\Http;

use Laminas\Http;
use Laminas\OAuth\Http as HTTPClient;
use Laminas\OAuth\OAuth;
use Laminas\OAuth\Token;

/**
 * @category   Laminas
 * @package    Laminas_OAuth
 */
class AccessToken extends HTTPClient
{
    /**
     * Singleton instance if required of the HTTP client
     *
     * @var \Laminas\Http\Client
     */
    protected $httpClient = null;

    /**
     * Initiate a HTTP request to retrieve an Access Token.
     *
     * @return \Laminas\OAuth\Token\Access
     */
    public function execute()
    {
        $params   = $this->assembleParams();
        $response = $this->startRequestCycle($params);
        $return   = new Token\Access($response);
        return $return;
    }

    /**
     * Assemble all parameters for an OAuth Access Token request.
     *
     * @return array
     */
    public function assembleParams()
    {
        $params = [
            'oauth_consumer_key'     => $this->consumer->getConsumerKey(),
            'oauth_nonce'            => $this->httpUtility->generateNonce(),
            'oauth_signature_method' => $this->consumer->getSignatureMethod(),
            'oauth_timestamp'        => $this->httpUtility->generateTimestamp(),
            'oauth_token'            => $this->consumer->getLastRequestToken()->getToken(),
            'oauth_version'          => $this->consumer->getVersion(),
        ];

        if (! empty($this->parameters)) {
            $params = array_merge($params, $this->parameters);
        }

        $params['oauth_signature'] = $this->httpUtility->sign(
            $params,
            $this->consumer->getSignatureMethod(),
            $this->consumer->getConsumerSecret(),
            $this->consumer->getLastRequestToken()->getTokenSecret(),
            $this->preferredRequestMethod,
            $this->consumer->getAccessTokenUrl()
        );

        return $params;
    }

    /**
     * Generate and return a HTTP Client configured for the Header Request Scheme
     * specified by OAuth, for use in requesting an Access Token.
     *
     * @param  array $params
     * @return Laminas\Http\Client
     */
    public function getRequestSchemeHeaderClient(array $params)
    {
        $params      = $this->cleanParamsOfIllegalCustomParameters($params);
        $headerValue = $this->toAuthorizationHeader($params);
        $client      = OAuth::getHttpClient();

        $client->setUri($this->consumer->getAccessTokenUrl());
        $client->setHeaders(['Authorization' => $headerValue]);
        $client->setMethod($this->preferredRequestMethod);

        return $client;
    }

    /**
     * Generate and return a HTTP Client configured for the POST Body Request
     * Scheme specified by OAuth, for use in requesting an Access Token.
     *
     * @param  array $params
     * @return Laminas\Http\Client
     */
    public function getRequestSchemePostBodyClient(array $params)
    {
        $params = $this->cleanParamsOfIllegalCustomParameters($params);
        $client = OAuth::getHttpClient();
        $client->setUri($this->consumer->getAccessTokenUrl());
        $client->setMethod($this->preferredRequestMethod);
        $client->setRawBody(
            $this->httpUtility->toEncodedQueryString($params)
        );
        $client->setHeaders(['Content-Type' => Http\Client::ENC_URLENCODED]);
        return $client;
    }

    /**
     * Generate and return a HTTP Client configured for the Query String Request
     * Scheme specified by OAuth, for use in requesting an Access Token.
     *
     * @param  array $params
     * @param  string $url
     * @return Laminas\Http\Client
     */
    public function getRequestSchemeQueryStringClient(array $params, $url)
    {
        $params = $this->cleanParamsOfIllegalCustomParameters($params);
        return parent::getRequestSchemeQueryStringClient($params, $url);
    }

    /**
     * Access Token requests specifically may not contain non-OAuth parameters.
     * So these should be striped out and excluded. Detection is easy since
     * specified OAuth parameters start with "oauth_", Extension params start
     * with "xouth_", and no other parameters should use these prefixes.
     *
     * xouth params are not currently allowable.
     *
     * @param  array $params
     * @return array
     */
    protected function cleanParamsOfIllegalCustomParameters(array $params)
    {
        foreach ($params as $key => $value) {
            if (! preg_match("/^oauth_/", $key)) {
                unset($params[$key]);
            }
        }
        return $params;
    }
}
