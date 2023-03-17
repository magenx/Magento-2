<?php

namespace Laminas\OAuth;

/**
 * @category   Laminas
 * @package    Laminas_OAuth
 */
class Http
{
    /**
     * Array of all custom service parameters to be sent in the HTTP request
     * in addition to the usual OAuth parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Reference to the Consumer instance in use.
     *
     * @var Consumer
     */
    protected $consumer = null;

    /**
     * OAuth specifies three request methods, this holds the current preferred
     * one which by default uses the Authorization Header approach for passing
     * OAuth parameters, and a POST body for non-OAuth custom parameters.
     *
     * @var string
     */
    protected $preferredRequestScheme = null;

    /**
     * Request Method for the HTTP Request.
     *
     * @var string
     */
    protected $preferredRequestMethod = OAuth::POST;

    /**
     * Instance of the general Laminas\OAuth\Http\Utility class.
     *
     * @var \Laminas\OAuth\Http\Utility
     */
    protected $httpUtility = null;

    /**
     * Constructor
     *
     * @param  \Laminas\OAuth\Consumer $consumer
     * @param  null|array $parameters
     * @param  null|\Laminas\OAuth\Http\Utility $utility
     * @return void
     */
    public function __construct(
        Consumer $consumer,
        array $parameters = null,
        Http\Utility $utility = null
    ) {
        $this->consumer = $consumer;
        $this->preferredRequestScheme = $this->consumer->getRequestScheme();
        if ($parameters !== null) {
            $this->setParameters($parameters);
        }
        if ($utility !== null) {
            $this->httpUtility = $utility;
        } else {
            $this->httpUtility = new Http\Utility;
        }
    }

    /**
     * Set a preferred HTTP request method.
     *
     * @param  string $method
     * @return Http
     * @throws Exception\InvalidArgumentException
     */
    public function setMethod($method)
    {
        if (! in_array($method, [OAuth::POST, OAuth::GET])) {
            throw new Exception\InvalidArgumentException('invalid HTTP method: ' . $method);
        }
        $this->preferredRequestMethod = $method;
        return $this;
    }

    /**
     * Preferred HTTP request method accessor.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->preferredRequestMethod;
    }

    /**
     * Mutator to set an array of custom parameters for the HTTP request.
     *
     * @param  array $customServiceParameters
     * @return Http
     */
    public function setParameters(array $customServiceParameters)
    {
        $this->parameters = $customServiceParameters;
        return $this;
    }

    /**
     * Accessor for an array of custom parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Return the Consumer instance in use.
     *
     * @return Consumer
     */
    public function getConsumer()
    {
        return $this->consumer;
    }

    /**
     * Commence a request cycle where the current HTTP method and OAuth
     * request scheme set an upper preferred HTTP request style and where
     * failures generate a new HTTP request style further down the OAuth
     * preference list for OAuth Request Schemes.
     * On success, return the Request object that results for processing.
     *
     * @todo   Remove cycling?; Replace with upfront do-or-die configuration
     * @param  array $params
     * @return \Laminas\Http\Response
     * @throws Exception\InvalidArgumentException on HTTP request errors
     */
    public function startRequestCycle(array $params)
    {
        $response = null;
        $body     = null;
        $status   = null;
        try {
            $response = $this->attemptRequest($params);
        } catch (\Laminas\Http\Client\Exception\ExceptionInterface $e) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Error in HTTP request: %s',
                $e->getMessage()
            ), null, $e);
        }
        if ($response !== null) {
            $body   = $response->getBody();
            $status = $response->getStatusCode();
        }
        if ($response === null // Request failure/exception
            || $status == 500  // Internal Server Error
            || $status == 400  // Bad Request
            || $status == 401  // Unauthorized
            || empty($body)    // Missing token
        ) {
            $this->assessRequestAttempt($response);
            $response = $this->startRequestCycle($params);
        }
        return $response;
    }

    /**
     * Return an instance of Laminas_Http_Client configured to use the Query
     * String scheme for an OAuth driven HTTP request.
     *
     * @param array $params
     * @param string $url
     * @return \Laminas\Http\Client
     */
    public function getRequestSchemeQueryStringClient(array $params, $url)
    {
        $client = OAuth::getHttpClient();
        $client->setUri($url);
        $client->getUri()->setQuery(
            $this->httpUtility->toEncodedQueryString($params)
        );
        $client->setMethod($this->preferredRequestMethod);
        return $client;
    }

    /**
     * Manages the switch from OAuth request scheme to another lower preference
     * scheme during a request cycle.
     *
     * @param  Laminas\Http\Response
     * @return void
     * @throws Exception\RuntimeException if unable to retrieve valid token response
     */
    protected function assessRequestAttempt(\Laminas\Http\Response $response = null)
    {
        switch ($this->preferredRequestScheme) {
            case OAuth::REQUEST_SCHEME_HEADER:
                $this->preferredRequestScheme = OAuth::REQUEST_SCHEME_POSTBODY;
                break;
            case OAuth::REQUEST_SCHEME_POSTBODY:
                $this->preferredRequestScheme = OAuth::REQUEST_SCHEME_QUERYSTRING;
                break;
            default:
                throw new Exception\RuntimeException(
                    'Could not retrieve a valid Token response from Token URL:'
                    . ($response !== null
                        ? PHP_EOL . $response->getBody()
                        : ' No body - check for headers')
                );
        }
    }

    /**
     * Generates a valid OAuth Authorization header based on the provided
     * parameters and realm.
     *
     * @param  array $params
     * @param  string $realm
     * @return string
     */
    protected function toAuthorizationHeader(array $params, $realm = null)
    {
        $headerValue = [];
        $headerValue[] = 'OAuth realm="' . $realm . '"';
        foreach ($params as $key => $value) {
            if (! preg_match("/^oauth_/", $key)) {
                continue;
            }
            $headerValue[] = Http\Utility::urlEncode($key)
                           . '="'
                           . Http\Utility::urlEncode($value)
                           . '"';
        }
        return implode(",", $headerValue);
    }

    /**
     * Attempt a request based on the current configured OAuth Request Scheme and
     * return the resulting HTTP Response.
     *
     * @param  array $params
     * @return \Laminas\Http\Response
     */
    protected function attemptRequest(array $params)
    {
        switch ($this->preferredRequestScheme) {
            case OAuth::REQUEST_SCHEME_HEADER:
                $httpClient = $this->getRequestSchemeHeaderClient($params);
                break;
            case OAuth::REQUEST_SCHEME_POSTBODY:
                $httpClient = $this->getRequestSchemePostBodyClient($params);
                break;
            case OAuth::REQUEST_SCHEME_QUERYSTRING:
                $httpClient = $this->getRequestSchemeQueryStringClient(
                    $params,
                    $this->consumer->getRequestTokenUrl()
                );
                break;
        }

        return $httpClient->send();
    }
}
