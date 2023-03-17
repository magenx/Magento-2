<?php

namespace LaminasTest\OAuth\TestAsset;

use Laminas\OAuth\Config\StandardConfig;
use Laminas\OAuth\Token\Access;

class Config90244 extends StandardConfig
{
    public function getConsumerKey()
    {
        return '1234567890';
    }

    public function getSignatureMethod()
    {
        return 'HMAC-SHA1';
    }

    public function getVersion()
    {
        return '1.0';
    }

    public function getRequestTokenUrl()
    {
        return 'http://www.example.com/request';
    }

    public function getToken()
    {
        $token = new Access();
        $token->setToken('abcde');
        return $token;
    }

    public function getRequestMethod()
    {
        return 'POST';
    }
}
