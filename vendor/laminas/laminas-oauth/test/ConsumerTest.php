<?php

namespace LaminasTest\OAuth;

use Laminas\OAuth\Consumer;
use Laminas\OAuth\Http;
use Laminas\OAuth\OAuth;
use Laminas\OAuth\Token;
use LaminasTest\OAuth\TestAsset\AccessToken48231;
use LaminasTest\OAuth\TestAsset\Consumer48231;
use LaminasTest\OAuth\TestAsset\RequestToken48231;
use PHPUnit\Framework\TestCase;

class ConsumerTest extends TestCase
{
    public function teardown(): void
    {
        OAuth::clearHttpClient();
    }

    public function testConstructorSetsConsumerKey()
    {
        $config = ['consumerKey' => '1234567890'];
        $consumer = new Consumer($config);
        $this->assertEquals('1234567890', $consumer->getConsumerKey());
    }

    public function testConstructorSetsConsumerSecret()
    {
        $config = ['consumerSecret' => '0987654321'];
        $consumer = new Consumer($config);
        $this->assertEquals('0987654321', $consumer->getConsumerSecret());
    }

    public function testSetsSignatureMethodFromOptionsArray()
    {
        $options = [
            'signatureMethod' => 'rsa-sha1'
        ];
        $consumer = new Consumer($options);
        $this->assertEquals('RSA-SHA1', $consumer->getSignatureMethod());
    }

    public function testSetsRequestMethodFromOptionsArray() // add back
    {
        $options = [
            'requestMethod' => OAuth::GET
        ];
        $consumer = new Consumer($options);
        $this->assertEquals(OAuth::GET, $consumer->getRequestMethod());
    }

    public function testSetsRequestSchemeFromOptionsArray()
    {
        $options = [
            'requestScheme' => OAuth::REQUEST_SCHEME_POSTBODY
        ];
        $consumer = new Consumer($options);
        $this->assertEquals(OAuth::REQUEST_SCHEME_POSTBODY, $consumer->getRequestScheme());
    }

    public function testSetsVersionFromOptionsArray()
    {
        $options = [
            'version' => '1.1'
        ];
        $consumer = new Consumer($options);
        $this->assertEquals('1.1', $consumer->getVersion());
    }

    public function testSetsCallbackUrlFromOptionsArray()
    {
        $options = [
            'callbackUrl' => 'http://www.example.com/local'
        ];
        $consumer = new Consumer($options);
        $this->assertEquals('http://www.example.com/local', $consumer->getCallbackUrl());
    }

    public function testSetsRequestTokenUrlFromOptionsArray()
    {
        $options = [
            'requestTokenUrl' => 'http://www.example.com/request'
        ];
        $consumer = new Consumer($options);
        $this->assertEquals('http://www.example.com/request', $consumer->getRequestTokenUrl());
    }

    public function testSetsUserAuthorizationUrlFromOptionsArray()
    {
        $options = [
            'userAuthorizationUrl' => 'http://www.example.com/authorize'
        ];
        $consumer = new Consumer($options);
        $this->assertEquals('http://www.example.com/authorize', $consumer->getUserAuthorizationUrl());
    }

    public function testSetsAccessTokenUrlFromOptionsArray()
    {
        $options = [
            'accessTokenUrl' => 'http://www.example.com/access'
        ];
        $consumer = new Consumer($options);
        $this->assertEquals('http://www.example.com/access', $consumer->getAccessTokenUrl());
    }

    public function testSetSignatureMethodThrowsExceptionForInvalidMethod()
    {
        $config = ['consumerKey' => '12345','consumerSecret' => '54321'];
        $consumer = new Consumer($config);

        $this->expectException('Laminas\OAuth\Exception\ExceptionInterface');
        $consumer->setSignatureMethod('buckyball');
    }

    public function testSetRequestMethodThrowsExceptionForInvalidMethod()
    {
        $config = ['consumerKey' => '12345','consumerSecret' => '54321'];
        $consumer = new Consumer($config);

        $this->expectException('Laminas\OAuth\Exception\ExceptionInterface');
        $consumer->setRequestMethod('buckyball');
    }

    public function testSetRequestSchemeThrowsExceptionForInvalidMethod()
    {
        $config = ['consumerKey' => '12345','consumerSecret' => '54321'];
        $consumer = new Consumer($config);

        $this->expectException('Laminas\OAuth\Exception\ExceptionInterface');
        $consumer->setRequestScheme('buckyball');
    }

    public function testSetLocalUrlThrowsExceptionForInvalidUrl()
    {
        $config = ['consumerKey' => '12345','consumerSecret' => '54321'];
        $consumer = new Consumer($config);

        $this->expectException('Laminas\OAuth\Exception\ExceptionInterface');
        $consumer->setLocalUrl('buckyball');
    }

    public function testSetRequestTokenUrlThrowsExceptionForInvalidUrl()
    {
        $config = ['consumerKey' => '12345','consumerSecret' => '54321'];
        $consumer = new Consumer($config);

        $this->expectException('Laminas\OAuth\Exception\ExceptionInterface');
        $consumer->setRequestTokenUrl('buckyball');
    }

    public function testSetUserAuthorizationUrlThrowsExceptionForInvalidUrl()
    {
        $config = ['consumerKey' => '12345','consumerSecret' => '54321'];
        $consumer = new Consumer($config);

        $this->expectException('Laminas\OAuth\Exception\ExceptionInterface');
        $consumer->setUserAuthorizationUrl('buckyball');
    }

    public function testSetAccessTokenUrlThrowsExceptionForInvalidUrl()
    {
        $config = ['consumerKey' => '12345','consumerSecret' => '54321'];
        $consumer = new Consumer($config);

        $this->expectException('Laminas\OAuth\Exception\ExceptionInterface');
        $consumer->setAccessTokenUrl('buckyball');
    }

    public function testGetRequestTokenReturnsInstanceOfOauthTokenRequest()
    {
        $config = ['consumerKey' => '12345','consumerSecret' => '54321'];
        $consumer = new Consumer($config);
        $token = $consumer->getRequestToken(null, null, new RequestToken48231());
        $this->assertInstanceOf('Laminas\OAuth\Token\Request', $token);
    }

    public function testGetRedirectUrlReturnsUserAuthorizationUrlWithParameters()
    {
        $config = ['consumerKey' => '12345','consumerSecret' => '54321',
            'userAuthorizationUrl' => 'http://www.example.com/authorize'];
        $consumer = new Consumer48231($config);
        $params = ['foo' => 'bar'];
        $uauth = new Http\UserAuthorization($consumer, $params);
        $token = new Token\Request;
        $token->setParams(['oauth_token' => '123456', 'oauth_token_secret' => '654321']);
        $redirectUrl = $consumer->getRedirectUrl($params, $token, $uauth);
        $this->assertEquals(
            // @codingStandardsIgnoreLine
            'http://www.example.com/authorize?oauth_token=123456&oauth_callback=http%3A%2F%2Fwww.example.com%2Flocal&foo=bar',
            $redirectUrl
        );
    }

    public function testGetAccessTokenReturnsInstanceOfOauthTokenAccess()
    {
        $config = ['consumerKey' => '12345','consumerSecret' => '54321'];
        $consumer = new Consumer($config);
        $rtoken = new Token\Request;
        $rtoken->setToken('token');
        $token = $consumer->getAccessToken(['oauth_token' => 'token'], $rtoken, null, new AccessToken48231());
        $this->assertInstanceOf('Laminas\OAuth\Token\Access', $token);
    }

    public function testGetLastRequestTokenReturnsInstanceWhenExists()
    {
        $config = ['consumerKey' => '12345','consumerSecret' => '54321'];
        $consumer = new Consumer48231($config);
        $this->assertInstanceOf('Laminas\OAuth\Token\Request', $consumer->getLastRequestToken());
    }

    public function testGetLastAccessTokenReturnsInstanceWhenExists()
    {
        $config = ['consumerKey' => '12345','consumerSecret' => '54321'];
        $consumer = new Consumer48231($config);
        $this->assertInstanceOf('Laminas\OAuth\Token\Access', $consumer->getLastAccessToken());
    }
}
