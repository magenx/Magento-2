<?php

namespace LaminasTest\OAuth\Token;

use Laminas\Http\Response as HTTPResponse;
use Laminas\OAuth\Token\Access as AccessToken;
use LaminasTest\OAuth\TestAsset\Config90244;
use LaminasTest\OAuth\TestAsset\HTTPUtility90244;
use PHPUnit\Framework\TestCase;

class AccessTest extends TestCase
{
    public function testConstructorSetsResponseObject()
    {
        $response = new HTTPResponse(200, []);
        $token = new AccessToken($response);
        $this->assertInstanceOf('Laminas\\Http\\Response', $token->getResponse());
    }

    public function testConstructorParsesRequestTokenFromResponseBody()
    {
        $body   = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $response = new HTTPResponse;
        $response->setContent($body)
                 ->setStatusCode(200);

        $token = new AccessToken($response);
        $this->assertEquals('jZaee4GF52O3lUb9', $token->getToken());
    }

    public function testConstructorParsesRequestTokenSecretFromResponseBody()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $response = new HTTPResponse;
        $response->setContent($body)
                 ->setStatusCode(200);

        $token = new AccessToken($response);
        $this->assertEquals('J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri', $token->getTokenSecret());
    }

    public function testPropertyAccessWorks()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri&foo=bar';
        $response = new HTTPResponse;
        $response->setContent($body)
                 ->setStatusCode(200);

        $token = new AccessToken($response);
        $this->assertEquals('J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri', $token->oauth_token_secret);
    }

    public function testTokenCastsToEncodedResponseBody()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $token = new AccessToken();
        $token->setToken('jZaee4GF52O3lUb9');
        $token->setTokenSecret('J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri');
        $this->assertEquals($body, (string) $token);
    }

    public function testToStringReturnsEncodedResponseBody()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $token = new AccessToken();
        $token->setToken('jZaee4GF52O3lUb9');
        $token->setTokenSecret('J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri');
        $this->assertEquals($body, $token->toString());
    }

    public function testIsValidDetectsBadResponse()
    {
        $body = 'oauthtoken=jZaee4GF52O3lUb9&oauthtokensecret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $response = new HTTPResponse;
        $response->setContent($body)
                 ->setStatusCode(200);

        $token = new AccessToken($response);
        $this->assertFalse($token->isValid());
    }

    public function testIsValidDetectsGoodResponse()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $response = new HTTPResponse;
        $response->setContent($body)
                 ->setStatusCode(200);

        $token = new AccessToken($response);
        $this->assertTrue($token->isValid());
    }

    public function testToHeaderReturnsValidHeaderString()
    {
        $token = new AccessToken(null, new HTTPUtility90244());
        $value = $token->toHeader(
            'http://www.example.com',
            new Config90244()
        );
        // @codingStandardsIgnoreLine
        $this->assertEquals('OAuth realm="",oauth_consumer_key="1234567890",oauth_nonce="e807f1fcf82d132f9bb018ca6738a19f",oauth_signature_method="HMAC-SHA1",oauth_timestamp="12345678901",oauth_version="1.0",oauth_token="abcde",oauth_signature="6fb42da0e32e07b61c9f0251fe627a9c"', $value);
    }
}
