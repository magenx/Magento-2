<?php

namespace LaminasTest\OAuth\Config;

use Laminas\OAuth\Config\StandardConfig;
use PHPUnit\Framework\TestCase;

class StandardConfigurationTest extends TestCase
{
    public function testSiteUrlArePropertlyBuiltFromDefaultPaths()
    {
        $config = new StandardConfig(
            [
                'siteUrl'   => 'https://example.com/oauth/'
            ]
        );
        $this->assertEquals('https://example.com/oauth/authorize', $config->getAuthorizeUrl());
        $this->assertEquals('https://example.com/oauth/request_token', $config->getRequestTokenUrl());
        $this->assertEquals('https://example.com/oauth/access_token', $config->getAccessTokenUrl());
    }
}
