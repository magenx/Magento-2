<?php

namespace LaminasTest\OAuth\TestAsset;

use Laminas\OAuth\Consumer;

class Consumer39745 extends Consumer
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

    public function getAccessTokenUrl()
    {
        return 'http://www.example.com/access';
    }

    public function getLastRequestToken()
    {
        $return = new RequestToken39745();
        return $return;
    }
}
