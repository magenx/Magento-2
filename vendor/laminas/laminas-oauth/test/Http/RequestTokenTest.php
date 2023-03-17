<?php

namespace LaminasTest\OAuth\Http;

use Laminas\OAuth\Http;
use Laminas\OAuth\OAuth;
use LaminasTest\OAuth\TestAsset\Consumer32874;
use LaminasTest\OAuth\TestAsset\Consumer32874b;
use LaminasTest\OAuth\TestAsset\HTTPClient32874;
use LaminasTest\OAuth\TestAsset\HTTPUtility32874;
use PHPUnit\Framework\TestCase;

class RequestTokenTest extends TestCase
{
    protected $stubConsumer = null;

    public function setup(): void
    {
        $this->stubConsumer = new Consumer32874();
        $this->stubConsumer2 = new Consumer32874b();
        $this->stubHttpUtility = new HTTPUtility32874();
        OAuth::setHttpClient(new HTTPClient32874());
    }

    public function teardown(): void
    {
        OAuth::clearHttpClient();
    }

    public function testConstructorSetsConsumerInstance()
    {
        $request = new Http\RequestToken($this->stubConsumer, null, $this->stubHttpUtility);
        $this->assertInstanceOf(Consumer32874::class, $request->getConsumer());
    }

    public function testConstructorSetsCustomServiceParameters()
    {
        $request = new Http\RequestToken($this->stubConsumer, [1,2,3], $this->stubHttpUtility);
        $this->assertEquals([1,2,3], $request->getParameters());
    }

    public function testAssembleParametersCorrectlyAggregatesOauthParameters()
    {
        $request = new Http\RequestToken($this->stubConsumer, null, $this->stubHttpUtility);
        $expectedParams = [
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_timestamp' => '12345678901',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_version' => '1.0',
            'oauth_callback' => 'http://www.example.com/local',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c'
        ];
        $this->assertEquals($expectedParams, $request->assembleParams());
    }

    public function testAssembleParametersCorrectlyAggregatesOauthParametersIfCallbackUrlMissing()
    {
        $request = new Http\RequestToken($this->stubConsumer2, null, $this->stubHttpUtility);
        $expectedParams = [
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_timestamp' => '12345678901',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_version' => '1.0',
            'oauth_callback' => 'oob', // out-of-band when missing callback - 1.0a
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c'

        ];
        $this->assertEquals($expectedParams, $request->assembleParams());
    }

    public function testAssembleParametersCorrectlyAggregatesCustomParameters()
    {
        $request = new Http\RequestToken($this->stubConsumer, [
            'custom_param1' => 'foo',
            'custom_param2' => 'bar'
        ], $this->stubHttpUtility);
        $expectedParams = [
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_timestamp' => '12345678901',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_version' => '1.0',
            'oauth_callback' => 'http://www.example.com/local',
            'custom_param1' => 'foo',
            'custom_param2' => 'bar',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c'
        ];
        $this->assertEquals($expectedParams, $request->assembleParams());
    }

    public function testGetRequestSchemeHeaderClientSetsCorrectlyEncodedAuthorizationHeader()
    {
        $request = new Http\RequestToken($this->stubConsumer, null, $this->stubHttpUtility);
        $params = [
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '12345678901',
            'oauth_version' => '1.0',
            'oauth_callback_url' => 'http://www.example.com/local',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c~',
            'custom_param1' => 'foo',
            'custom_param2' => 'bar'
        ];
        $client = $request->getRequestSchemeHeaderClient($params);
        $this->assertEquals(
            'OAuth realm="",oauth_consumer_key="1234567890",oauth_nonce="e807f1fcf82d132f9b'
            .'b018ca6738a19f",oauth_signature_method="HMAC-SHA1",oauth_timestamp="'
            .'12345678901",oauth_version="1.0",oauth_callback_url='
            .'"http%3A%2F%2Fwww.example.com%2Flocal",oauth_signature="6fb42da0e32e07b61c9f0251fe627a9c~"',
            $client->getHeader('Authorization')
        );
    }

    public function testGetRequestSchemePostBodyClientSetsCorrectlyEncodedRawData()
    {
        $request = new Http\RequestToken($this->stubConsumer, null, $this->stubHttpUtility);
        $params = [
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '12345678901',
            'oauth_version' => '1.0',
            'oauth_callback_url' => 'http://www.example.com/local',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c~',
            'custom_param1' => 'foo',
            'custom_param2' => 'bar'
        ];
        $client = $request->getRequestSchemePostBodyClient($params);
        $this->assertEquals(
            'oauth_consumer_key=1234567890&oauth_nonce=e807f1fcf82d132f9bb018c'
            .'a6738a19f&oauth_signature_method=HMAC-SHA1&oauth_timestamp=12345'
            .'678901&oauth_version=1.0&oauth_callback_url=http%3A%2F%2Fwww.example.com%2Flocal'
            .'&oauth_signature=6fb42da0e32e07b61c9f0251fe627a9c~'
            .'&custom_param1=foo&custom_param2=bar',
            $client->getRawData()
        );
    }

    public function testGetRequestSchemeQueryStringClientSetsCorrectlyEncodedQueryString()
    {
        $request = new Http\RequestToken($this->stubConsumer, null, $this->stubHttpUtility);
        $params = [
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '12345678901',
            'oauth_version' => '1.0',
            'oauth_callback_url' => 'http://www.example.com/local',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c',
            'custom_param1' => 'foo',
            'custom_param2' => 'bar'
        ];
        $client = $request->getRequestSchemeQueryStringClient($params, 'http://www.example.com');
        $this->assertEquals(
            'oauth_consumer_key=1234567890&oauth_nonce=e807f1fcf82d132f9bb018c'
            .'a6738a19f&oauth_signature_method=HMAC-SHA1&oauth_timestamp=12345'
            .'678901&oauth_version=1.0&oauth_callback_url=http%3A%2F%2Fwww.example.com%2Flocal'
            .'&oauth_signature=6fb42da0e32e07b61c9f0251fe627a9c'
            .'&custom_param1=foo&custom_param2=bar',
            $client->getUri()->getQuery()
        );
    }
}
