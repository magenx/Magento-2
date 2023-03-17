<?php

namespace LaminasTest\OAuth\TestAsset;

use Laminas\OAuth\Consumer;

class Consumer32874 extends Consumer
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

    public function getCallbackUrl()
    {
        return 'http://www.example.com/local';
    }
}
