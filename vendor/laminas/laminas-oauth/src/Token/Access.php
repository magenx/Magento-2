<?php

namespace Laminas\OAuth\Token;

use Laminas\OAuth\Client;
use Laminas\OAuth\Config\ConfigInterface as Config;
use Laminas\OAuth\Exception;
use Laminas\Uri;

/**
 * @category   Laminas
 * @package    Laminas_OAuth
 */
class Access extends AbstractToken
{
    /**
     * Cast to HTTP header
     *
     * @param  string $url
     * @param  Config $config
     * @param  null|array $customParams
     * @param  null|string $realm
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function toHeader(
        $url,
        Config $config,
        array $customParams = null,
        $realm = null
    ) {
        $uri = Uri\UriFactory::factory($url);
        if (! $uri->isValid()
            || ! in_array($uri->getScheme(), ['http', 'https'])
        ) {
            throw new Exception\InvalidArgumentException(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $params = $this->httpUtility->assembleParams($url, $config, $customParams);
        return $this->httpUtility->toAuthorizationHeader($params, $realm);
    }

    /**
     * Cast to HTTP query string
     *
     * @param  mixed $url
     * @param  Laminas\OAuth\Config $config
     * @param  null|array $params
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function toQueryString($url, Config $config, array $params = null)
    {
        $uri = Uri\UriFactory::factory($url);
        if (! $uri->isValid()
            || ! in_array($uri->getScheme(), ['http', 'https'])
        ) {
            throw new Exception\InvalidArgumentException(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $params = $this->httpUtility->assembleParams($url, $config, $params);
        return $this->httpUtility->toEncodedQueryString($params);
    }

    /**
     * Get OAuth client
     *
     * @param  array $oauthOptions
     * @param  null|string $uri
     * @param  null|array|\Traversable $config
     * @param  bool $excludeCustomParamsFromHeader
     * @return Client
     */
    public function getHttpClient(
        array $oauthOptions,
        $uri = null,
        $config = null,
        $excludeCustomParamsFromHeader = true
    ) {
        $client = new Client($oauthOptions, $uri, $config, $excludeCustomParamsFromHeader);
        $client->setToken($this);
        return $client;
    }
}
