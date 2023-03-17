<?php

namespace Laminas\OAuth;

use Laminas\Http\Client as HTTPClient;

/**
 * @category   Laminas
 * @package    Laminas_OAuth
 */
class OAuth
{
    const REQUEST_SCHEME_HEADER      = 'header';
    const REQUEST_SCHEME_POSTBODY    = 'postbody';
    const REQUEST_SCHEME_QUERYSTRING = 'querystring';
    const GET                        = 'GET';
    const POST                       = 'POST';
    const PUT                        = 'PUT';
    const DELETE                     = 'DELETE';
    const HEAD                       = 'HEAD';

    /**
     * Singleton instance if required of the HTTP client
     *
     * @var Laminas\Http\Client
     */
    protected static $httpClient = null;

    /**
     * Allows the external environment to make Laminas_OAuth use a specific
     * Client instance.
     *
     * @param Laminas\Http\Client $httpClient
     * @return void
     */
    public static function setHttpClient(HTTPClient $httpClient)
    {
        self::$httpClient = $httpClient;
    }

    /**
     * Return the singleton instance of the HTTP Client. Note that
     * the instance is reset and cleared of previous parameters and
     * Authorization header values.
     *
     * @return Laminas\Http\Client
     */
    public static function getHttpClient()
    {
        if (! isset(self::$httpClient)) {
            self::$httpClient = new HTTPClient;
        } else {
            $request = self::$httpClient->getRequest();
            $headers = $request->getHeaders();
            if ($headers->has('Authorization')) {
                $auth = $headers->get('Authorization');
                $headers->removeHeader($auth);
            }
            self::$httpClient->resetParameters();
        }
        return self::$httpClient;
    }

    /**
     * Simple mechanism to delete the entire singleton HTTP Client instance
     * which forces an new instantiation for subsequent requests.
     *
     * @return void
     */
    public static function clearHttpClient()
    {
        self::$httpClient = null;
    }
}
