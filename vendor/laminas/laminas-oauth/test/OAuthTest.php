<?php

namespace LaminasTest\OAuth;

use Laminas\Config\Config;
use Laminas\Http\Header;
use Laminas\OAuth\Client as OAuthClient;
use Laminas\OAuth\Http\Utility;
use Laminas\OAuth\OAuth;
use Laminas\OAuth\Token;
use LaminasTest\OAuth\TestAsset\HTTPClient19485876;
use PHPUnit\Framework\TestCase;

class OAuthTest extends TestCase
{
    public function teardown(): void
    {
        OAuth::clearHttpClient();
    }

    public function testCanSetCustomHttpClient()
    {
        OAuth::setHttpClient(new HTTPClient19485876());
        $this->assertInstanceOf(HTTPClient19485876::class, OAuth::getHttpClient());
    }

    public function testGetHttpClientResetsParameters()
    {
        $client = new HTTPClient19485876();
        $client->setParameterGet(['key' => 'value']);
        OAuth::setHttpClient($client);
        $resetClient = OAuth::getHttpClient();
        $resetClient->setUri('http://www.example.com');
        $this->assertEquals('http://www.example.com/', (string) $resetClient->getUri(true));
    }

    public function testGetHttpClientResetsAuthorizationHeader()
    {
        $client = new HTTPClient19485876();
        $client->setHeaders(['Authorization' => 'realm="http://www.example.com",oauth_version="1.0"']);
        OAuth::setHttpClient($client);
        $resetClient = OAuth::getHttpClient();
        $this->assertEquals(null, $resetClient->getHeader('Authorization'));
    }

    /**
     * @group Laminas-10182
     */
    public function testOauthClientPassingObjectConfigInConstructor()
    {
        $options = [
            'requestMethod' => 'GET',
            'siteUrl'       => 'http://www.example.com'
        ];

        $config = new Config($options);
        $client = new OAuthClient($config);
        $this->assertEquals('GET', $client->getRequestMethod());
        $this->assertEquals('http://www.example.com', $client->getSiteUrl());
    }

    /**
     * @group Laminas-10182
     */
    public function testOauthClientPassingArrayInConstructor()
    {
        $options = [
            'requestMethod' => 'GET',
            'siteUrl'       => 'http://www.example.com'
        ];

        $client = new OAuthClient($options);
        $this->assertEquals('GET', $client->getRequestMethod());
        $this->assertEquals('http://www.example.com', $client->getSiteUrl());
    }

    public function testOauthClientUsingGetRequestParametersForSignature()
    {
        $mock = $this->getMockBuilder(Utility::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateTimestamp', 'generateNonce'])
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('generateTimestamp')
            ->will($this->returnValue('123456789'));
        $mock
            ->expects($this->once())
            ->method('generateNonce')
            ->will($this->returnValue('67648c83ba9a7de429bd1b773fb96091'));

        $token = new Token\Access(null, $mock);
        $token->setToken('123')
              ->setTokenSecret('456');

        $client = new OAuthClient([
            'token' => $token
        ], 'http://www.example.com');
        $client->getRequest()->getQuery()->set('foo', 'bar');
        $client->prepareOAuth();

        // @codingStandardsIgnoreLine
        $header = 'OAuth realm="",oauth_consumer_key="",oauth_nonce="67648c83ba9a7de429bd1b773fb96091",oauth_signature_method="HMAC-SHA1",oauth_timestamp="123456789",oauth_version="1.0",oauth_token="123",oauth_signature="fzWiYe4gZ2wkEMp9bEzWnlD88KE%3D"';
        $this->assertEquals($header, $client->getHeader('Authorization'));
    }

    public function testOauthClientUsingPostRequestParametersForSignature()
    {
        $mock = $this->getMockBuilder(Utility::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateTimestamp', 'generateNonce'])
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('generateTimestamp')
            ->will($this->returnValue('123456789'));
        $mock
            ->expects($this->once())
            ->method('generateNonce')
            ->will($this->returnValue('67648c83ba9a7de429bd1b773fb96091'));

        $token = new Token\Access(null, $mock);
        $token->setToken('123')
              ->setTokenSecret('456');

        $client = new OAuthClient([
            'token' => $token
        ], 'http://www.example.com');
        $client->getRequest()->getPost()->set('foo', 'bar');
        $client->prepareOAuth();

        // @codingStandardsIgnoreLine
        $header = 'OAuth realm="",oauth_consumer_key="",oauth_nonce="67648c83ba9a7de429bd1b773fb96091",oauth_signature_method="HMAC-SHA1",oauth_timestamp="123456789",oauth_version="1.0",oauth_token="123",oauth_signature="fzWiYe4gZ2wkEMp9bEzWnlD88KE%3D"';
        $this->assertEquals($header, $client->getHeader('Authorization'));
    }

    public function testOauthClientUsingPostAndGetRequestParametersForSignature()
    {
        $mock = $this->getMockBuilder(Utility::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateTimestamp', 'generateNonce'])
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('generateTimestamp')
            ->will($this->returnValue('123456789'));
        $mock
            ->expects($this->once())
            ->method('generateNonce')
            ->will($this->returnValue('67648c83ba9a7de429bd1b773fb96091'));

        $token = new Token\Access(null, $mock);
        $token->setToken('123')
              ->setTokenSecret('456');

        $client = new OAuthClient([
            'token' => $token
        ], 'http://www.example.com');
        $client->getRequest()->getPost()->set('foo', 'bar');
        $client->getRequest()->getQuery()->set('baz', 'bat');
        $client->prepareOAuth();

        // @codingStandardsIgnoreLine
        $header = 'OAuth realm="",oauth_consumer_key="",oauth_nonce="67648c83ba9a7de429bd1b773fb96091",oauth_signature_method="HMAC-SHA1",oauth_timestamp="123456789",oauth_version="1.0",oauth_token="123",oauth_signature="qj3FYtStzP083hT9QkqCdxsMauw%3D"';
        $this->assertEquals($header, $client->getHeader('Authorization'));
    }


    public function testOAuthClientDoesntOverrideExistingHeaders()
    {
        $mock = $this->getMockBuilder(Utility::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateTimestamp', 'generateNonce'])
            ->getMock();
        $mock
            ->expects($this->once())
            ->method('generateTimestamp')
            ->will($this->returnValue('123456789'));
        $mock
            ->expects($this->once())
            ->method('generateNonce')
            ->will($this->returnValue('67648c83ba9a7de429bd1b773fb96091'));

        $token = new Token\Access(null, $mock);
        $token->setToken('123')
              ->setTokenSecret('456');

        $client = new OAuthClient([
            'token' => $token
        ], 'http://www.example.com');

        $dummyHeader = Header\ContentType::fromString('Content-Type: application/octet-stream');
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([$dummyHeader]);
        $client->prepareOAuth();

        $this->assertTrue($client->getRequest()->getHeaders()->has('Content-Type'));
        $this->assertEquals($dummyHeader, $client->getRequest()->getHeaders()->get('Content-Type'));
    }
}
