<?php

namespace LaminasTest\OAuth\TestAsset;

use Laminas\OAuth\Http\AccessToken;
use Laminas\OAuth\Token\Access;

class AccessToken48231 extends AccessToken
{
    public function __construct()
    {
    }

    public function execute(array $params = null)
    {
        $return = new Access();
        return $return;
    }

    public function setParams(array $customServiceParameters)
    {
    }
}
