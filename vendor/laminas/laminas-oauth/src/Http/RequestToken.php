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
class RequestToken extends HTTPClient
{
    /**
     * Singleton instance if required of the HTTP client
     *
     * @var Http\Client
     */
    protected $httpClient = null;

    /**
     * Initiate a HTTP request to retrieve a Request Token.
     *
     * @return Token\Request
     */
    public function execute()
    {
        $params   = $this->assembleParams();
        $response = $this->startRequestCycle($params);
        $return   = new Token\Request($response);
        return $return;
    }

    /**
     * Assemble all parameters for an OAuth Request Token request.
     *
     * @return array
     */
    public function assembleParams()
    {
        $params = [
            'oauth_consumer_key'     => $this->consumer->getConsumerKey(),
            'oauth_nonce'            => $this->httpUtility->generateNonce(),
            'oauth_timestamp'        => $this->httpUtility->generateTimestamp(),
            'oauth_signature_method' => $this->consumer->getSignatureMethod(),
            'oauth_version'          => $this->consumer->getVersion(),
        ];

        // indicates we support 1.0a
        if ($this->consumer->getCallbackUrl()) {
            $params['oauth_callback'] = $this->consumer->getCallbackUrl();
        } else {
            $params['oauth_callback'] = 'oob';
        }

        if (! empty($this->parameters)) {
            $params = array_merge($params, $this->parameters);
        }

        $params['oauth_signature'] = $this->httpUtility->sign(
            $params,
            $this->consumer->getSignatureMethod(),
            $this->consumer->getConsumerSecret(),
            null,
            $this->preferredRequestMethod,
            $this->consumer->getRequestTokenUrl()
        );

        return $params;
    }

    /**
     * Generate and return a HTTP Client configured for the Header Request Scheme
     * specified by OAuth, for use in requesting a Request Token.
     *
     * @param array $params
     * @return Http\Client
     */
    public function getRequestSchemeHeaderClient(array $params)
    {
        $headerValue = $this->httpUtility->toAuthorizationHeader(
            $params
        );

        $client = OAuth::getHttpClient();

        $client->setUri($this->consumer->getRequestTokenUrl());

        $request = $client->getRequest();
        $request->getHeaders()
                ->addHeaderLine('Authorization', $headerValue);
        $rawdata = $this->httpUtility->toEncodedQueryString($params, true);
        if (! empty($rawdata)) {
            $request->setContent($rawdata);
        }

        $client->setMethod($this->preferredRequestMethod);
        return $client;
    }

    /**
     * Generate and return a HTTP Client configured for the POST Body Request
     * Scheme specified by OAuth, for use in requesting a Request Token.
     *
     * @param  array $params
     * @return Http\Client
     */
    public function getRequestSchemePostBodyClient(array $params)
    {
        $client = OAuth::getHttpClient();
        $client->setUri($this->consumer->getRequestTokenUrl());
        $client->setMethod($this->preferredRequestMethod);
        $request = $client->getRequest();
        $request->setContent(
            $this->httpUtility->toEncodedQueryString($params)
        );
        $request->getHeaders()
                ->addHeaderLine('Content-Type', Http\Client::ENC_URLENCODED);
        return $client;
    }
}
