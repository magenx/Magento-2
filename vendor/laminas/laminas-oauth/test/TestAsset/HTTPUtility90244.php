<?php

namespace LaminasTest\OAuth\TestAsset;

use Laminas\OAuth\Http\Utility;

class HTTPUtility90244 extends Utility
{
    public function __construct()
    {
    }

    public function generateNonce()
    {
        return md5('1234567890');
    }

    public function generateTimestamp()
    {
        return '12345678901';
    }

    public function sign(
        array $params,
        $signatureMethod,
        $consumerSecret,
        $accessTokenSecret = null,
        $method = null,
        $url = null
    ) {
        return md5('0987654321');
    }
}
