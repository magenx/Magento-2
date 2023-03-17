<?php

namespace LaminasTest\OAuth\Http;

use Laminas\OAuth\Http;
use Laminas\OAuth\OAuth;
use LaminasTest\OAuth\TestAsset\Consumer39745;
use LaminasTest\OAuth\TestAsset\HTTPClient39745;
use LaminasTest\OAuth\TestAsset\HTTPUtility39745;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{
    protected $stubConsumer = null;

    public function setup(): void
    {
        $this->stubConsumer = new Consumer39745();
        $this->stubHttpUtility = new HTTPUtility39745();
        OAuth::setHttpClient(new HTTPClient39745());
    }

    public function teardown(): void
    {
        OAuth::clearHttpClient();
    }

    public function testConstructorSetsConsumerInstance()
    {
        $request = new Http\AccessToken($this->stubConsumer, null, $this->stubHttpUtility);
        $this->assertInstanceOf(Consumer39745::class, $request->getConsumer());
    }

    public function testConstructorSetsCustomServiceParameters()
    {
        $request = new Http\AccessToken($this->stubConsumer, [1,2,3], $this->stubHttpUtility);
        $this->assertEquals([1,2,3], $request->getParameters());
    }

    public function testAssembleParametersCorrectlyAggregatesOauthParameters()
    {
        $request = new Http\AccessToken($this->stubConsumer, null, $this->stubHttpUtility);
        $expectedParams = [
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '12345678901',
            'oauth_token' => '0987654321',
            'oauth_version' => '1.0',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c'
        ];
        $this->assertEquals($expectedParams, $request->assembleParams());
    }
    public function testAssembleParametersCorrectlyIgnoresCustomParameters()
    {
        $request = new Http\AccessToken($this->stubConsumer, [
            'custom_param1' => 'foo',
            'custom_param2' => 'bar'
        ], $this->stubHttpUtility);
        $expectedParams = [
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '12345678901',
            'oauth_token' => '0987654321',
            'oauth_version' => '1.0',
            'custom_param1' => 'foo',
            'custom_param2' => 'bar',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c'
        ];
        $this->assertEquals($expectedParams, $request->assembleParams());
    }

    public function testGetRequestSchemeHeaderClientSetsCorrectlyEncodedAuthorizationHeader()
    {
        $request = new Http\AccessToken($this->stubConsumer, null, $this->stubHttpUtility);
        $params = [
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '12345678901',
            'oauth_token' => '0987654321',
            'oauth_version' => '1.0',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c~',
            'custom_param1' => 'foo',
            'custom_param2' => 'bar'
        ];
        $client = $request->getRequestSchemeHeaderClient($params);
        $this->assertEquals(
            'OAuth realm="",oauth_consumer_key="1234567890",oauth_nonce="e807f1fcf82d132f9b'
            .'b018ca6738a19f",oauth_signature_method="HMAC-SHA1",oauth_timestamp="'
            .'12345678901",oauth_token="0987654321",oauth_version="1.0",oauth_sign'
            .'ature="6fb42da0e32e07b61c9f0251fe627a9c~"',
            $client->getHeader('Authorization')
        );
    }

    public function testGetRequestSchemePostBodyClientSetsCorrectlyEncodedRawData()
    {
        $request = new Http\AccessToken($this->stubConsumer, null, $this->stubHttpUtility);
        $params = [
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '12345678901',
            'oauth_token' => '0987654321',
            'oauth_version' => '1.0',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c~',
            'custom_param1' => 'foo',
            'custom_param2' => 'bar'
        ];
        $client = $request->getRequestSchemePostBodyClient($params);
        $this->assertEquals(
            'oauth_consumer_key=1234567890&oauth_nonce=e807f1fcf82d132f9bb018c'
            .'a6738a19f&oauth_signature_method=HMAC-SHA1&oauth_timestamp=12345'
            .'678901&oauth_token=0987654321&oauth_version=1.0&oauth_signature='
            .'6fb42da0e32e07b61c9f0251fe627a9c~',
            //$client->getRawData()
            $client->getRequest()->getContent()
        );
    }

    public function testGetRequestSchemeQueryStringClientSetsCorrectlyEncodedQueryString()
    {
        $request = new Http\AccessToken($this->stubConsumer, null, $this->stubHttpUtility);
        $params = [
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '12345678901',
            'oauth_token' => '0987654321',
            'oauth_version' => '1.0',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c',
            'custom_param1' => 'foo',
            'custom_param2' => 'bar'
        ];
        $client = $request->getRequestSchemeQueryStringClient($params, 'http://www.example.com');
        $this->assertEquals(
            'oauth_consumer_key=1234567890&oauth_nonce=e807f1fcf82d132f9bb018c'
            .'a6738a19f&oauth_signature_method=HMAC-SHA1&oauth_timestamp=12345'
            .'678901&oauth_token=0987654321&oauth_version=1.0&oauth_signature='
            .'6fb42da0e32e07b61c9f0251fe627a9c',
            $client->getUri()->getQuery()
        );
    }
}
