<?php

namespace Laminas\OAuth;

use Laminas\Stdlib\ArrayUtils;
use Traversable;

/**
 * @category   Laminas
 * @package    Laminas_OAuth
 */
class Consumer extends OAuth
{
    public $switcheroo = false; // replace later when this works

    /**
     * Request Token retrieved from OAuth Provider
     *
     * @var \Laminas\OAuth\Token\Request
     */
    protected $requestToken = null;

    /**
     * Access token retrieved from OAuth Provider
     *
     * @var \Laminas\OAuth\Token\Access
     */
    protected $accessToken = null;

    /**
     * @var \Laminas\OAuth\Config
     */
    protected $config = null;

    /**
     * Constructor; create a new object with an optional array|Laminas_Config
     * instance containing initialising options.
     *
     * @param  array|Traversable $options
     */
    public function __construct($options = null)
    {
        $this->config = new Config\StandardConfig;
        if ($options !== null) {
            if ($options instanceof Traversable) {
                $options = ArrayUtils::iteratorToArray($options);
            }
            $this->config->setOptions($options);
        }
    }

    /**
     * Attempts to retrieve a Request Token from an OAuth Provider which is
     * later exchanged for an authorized Access Token used to access the
     * protected resources exposed by a web service API.
     *
     * @param  null|array $customServiceParameters Non-OAuth Provider-specified parameters
     * @param  null|string $httpMethod
     * @param  null|Laminas\OAuth\Http\RequestToken $request
     * @return Laminas\OAuth\Token\Request
     */
    public function getRequestToken(
        array $customServiceParameters = null,
        $httpMethod = null,
        Http\RequestToken $request = null
    ) {
        if ($request === null) {
            $request = new Http\RequestToken($this, $customServiceParameters);
        } elseif ($customServiceParameters !== null) {
            $request->setParameters($customServiceParameters);
        }
        if ($httpMethod !== null) {
            $request->setMethod($httpMethod);
        } else {
            $request->setMethod($this->getRequestMethod());
        }

        $this->requestToken = $request->execute();
        return $this->requestToken;
    }

    /**
     * After a Request Token is retrieved, the user may be redirected to the
     * OAuth Provider to authorize the application's access to their
     * protected resources - the redirect URL being provided by this method.
     * Once the user has authorized the application for access, they are
     * redirected back to the application which can now exchange the previous
     * Request Token for a fully authorized Access Token.
     *
     * @param  null|array $customServiceParameters
     * @param  null|Laminas\OAuth\Token\Request $token
     * @param  null|Laminas\OAuth\HTTP\UserAuthorization $redirect
     * @return string
     */
    public function getRedirectUrl(
        array $customServiceParameters = null,
        Token\Request $token = null,
        Http\UserAuthorization $redirect = null
    ) {
        if ($redirect === null) {
            $redirect = new Http\UserAuthorization($this, $customServiceParameters);
        } elseif ($customServiceParameters !== null) {
            $redirect->setParameters($customServiceParameters);
        }
        if ($token !== null) {
            $this->requestToken = $token;
        }
        return $redirect->getUrl();
    }

    /**
     * Rather than retrieve a redirect URL for use, e.g. from a controller,
     * one may perform an immediate redirect.
     *
     * Sends headers and exit()s on completion.
     *
     * @param  null|array $customServiceParameters
     * @param  null|Laminas\OAuth\Http\UserAuthorization $request
     * @return void
     */
    public function redirect(
        array $customServiceParameters = null,
        Http\UserAuthorization $request = null
    ) {
        $redirectUrl = $this->getRedirectUrl($customServiceParameters, $request);
        header('Location: ' . $redirectUrl);
        exit(1);
    }

    /**
     * Retrieve an Access Token in exchange for a previously received/authorized
     * Request Token.
     *
     * @param  array $queryData GET data returned in user's redirect from Provider
     * @param  \Laminas\OAuth\Token\Request Request Token information
     * @param  string $httpMethod
     * @param  \Laminas\OAuth\Http\AccessToken $request
     * @return \Laminas\OAuth\Token\Access
     * @throws Exception\InvalidArgumentException on invalid authorization
     *     token, non-matching response authorization token, or unprovided
     *     authorization token
     */
    public function getAccessToken(
        $queryData,
        Token\Request $token,
        $httpMethod = null,
        Http\AccessToken $request = null
    ) {
        $authorizedToken = new Token\AuthorizedRequest($queryData);
        if (! $authorizedToken->isValid()) {
            throw new Exception\InvalidArgumentException(
                'Response from Service Provider is not a valid authorized request token'
            );
        }
        if ($request === null) {
            $request = new Http\AccessToken($this);
        }

        // OAuth 1.0a Verifier
        if ($authorizedToken->getParam('oauth_verifier') !== null) {
            $params = array_merge($request->getParameters(), [
                'oauth_verifier' => $authorizedToken->getParam('oauth_verifier')
            ]);
            $request->setParameters($params);
        }
        if ($httpMethod !== null) {
            $request->setMethod($httpMethod);
        } else {
            $request->setMethod($this->getRequestMethod());
        }
        if (isset($token)) {
            if ($authorizedToken->getToken() !== $token->getToken()) {
                throw new Exception\InvalidArgumentException(
                    'Authorized token from Service Provider does not match'
                    . ' supplied Request Token details'
                );
            }
        } else {
            throw new Exception\InvalidArgumentException('Request token must be passed to method');
        }
        $this->requestToken = $token;
        $this->accessToken = $request->execute();
        return $this->accessToken;
    }

    /**
     * Return whatever the last Request Token retrieved was while using the
     * current Consumer instance.
     *
     * @return \Laminas\OAuth\Token\Request
     */
    public function getLastRequestToken()
    {
        return $this->requestToken;
    }

    /**
     * Return whatever the last Access Token retrieved was while using the
     * current Consumer instance.
     *
     * @return \Laminas\OAuth\Token\Access
     */
    public function getLastAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Alias to self::getLastAccessToken()
     *
     * @return \Laminas\OAuth\Token\Access
     */
    public function getToken()
    {
        return $this->accessToken;
    }

    /**
     * Simple Proxy to the current Laminas_OAuth_Config method. It's that instance
     * which holds all configuration methods and values this object also presents
     * as it's API.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Exception\BadMethodCallException if method does not exist in config object
     */
    public function __call($method, array $args)
    {
        if (! method_exists($this->config, $method)) {
            throw new Exception\BadMethodCallException('Method does not exist: '.$method);
        }
        return call_user_func_array([$this->config, $method], $args);
    }
}
