<?php

namespace LaminasTest\OAuth\Http;

use Laminas\OAuth\Http;
use LaminasTest\OAuth\TestAsset\Consumer34879;
use PHPUnit\Framework\TestCase;

class UserAuthorizationTest extends TestCase
{
    protected $stubConsumer = null;

    public function setup(): void
    {
        $this->stubConsumer = new Consumer34879();
    }

    public function testConstructorSetsConsumerInstance()
    {
        $redirect = new Http\UserAuthorization($this->stubConsumer);
        $this->assertInstanceOf(Consumer34879::class, $redirect->getConsumer());
    }

    public function testConstructorSetsCustomServiceParameters()
    {
        $redirect = new Http\UserAuthorization($this->stubConsumer, [1,2,3]);
        $this->assertEquals([1,2,3], $redirect->getParameters());
    }

    public function testAssembleParametersReturnsUserAuthorizationParamArray()
    {
        $redirect = new Http\UserAuthorization($this->stubConsumer, ['foo ' => 'bar~']);
        $expected = [
            'oauth_token' => '1234567890',
            'oauth_callback' => 'http://www.example.com/local',
            'foo ' => 'bar~'
        ];
        $this->assertEquals($expected, $redirect->assembleParams());
    }

    public function testGetUrlReturnsEncodedQueryStringParamsAppendedToLocalUrl()
    {
        $redirect = new Http\UserAuthorization($this->stubConsumer, ['foo ' => 'bar~']);
        // @codingStandardsIgnoreLine
        $expected = 'http://www.example.com/authorize?oauth_token=1234567890&oauth_callback=http%3A%2F%2Fwww.example.com%2Flocal&foo%20=bar~';
        $this->assertEquals($expected, $redirect->getUrl());
    }
}
