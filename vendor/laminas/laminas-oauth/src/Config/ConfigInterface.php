<?php

namespace Laminas\OAuth\Config;

use Laminas\OAuth\Token\TokenInterface;

/**
 * @category   Laminas
 * @package    Laminas_OAuth
 */
interface ConfigInterface
{
    public function setOptions(array $options);

    public function setConsumerKey($key);

    public function getConsumerKey();

    public function setConsumerSecret($secret);

    public function getConsumerSecret();

    public function setSignatureMethod($method);

    public function getSignatureMethod();

    public function setRequestScheme($scheme);

    public function getRequestScheme();

    public function setVersion($version);

    public function getVersion();

    public function setCallbackUrl($url);

    public function getCallbackUrl();

    public function setRequestTokenUrl($url);

    public function getRequestTokenUrl();

    public function setRequestMethod($method);

    public function getRequestMethod();

    public function setAccessTokenUrl($url);

    public function getAccessTokenUrl();

    public function setUserAuthorizationUrl($url);

    public function getUserAuthorizationUrl();

    public function setToken(TokenInterface $token);

    public function getToken();
}
