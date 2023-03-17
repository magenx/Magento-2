<?php

namespace LaminasTest\OAuth\Token;

use Laminas\Http\Response as HTTPResponse;
use Laminas\OAuth\Token\Request as RequestToken;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testConstructorSetsResponseObject()
    {
        $response = new HTTPResponse(200, []);
        $token = new RequestToken($response);
        $this->assertInstanceOf(HTTPResponse::class, $token->getResponse());
    }

    public function testConstructorParsesRequestTokenFromResponseBody()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $response = new HTTPResponse;
        $response->setContent($body)
                 ->setStatusCode(200);

        $token = new RequestToken($response);
        $this->assertEquals('jZaee4GF52O3lUb9', $token->getToken());
    }

    public function testConstructorParsesRequestTokenSecretFromResponseBody()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';

        $response = new HTTPResponse;
        $response->setContent($body)
                 ->setStatusCode(200);

        $token = new RequestToken($response);
        $this->assertEquals('J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri', $token->getTokenSecret());
    }

    public function testPropertyAccessWorks()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri&foo=bar';
        $response = new HTTPResponse;
        $response->setContent($body)
                 ->setStatusCode(200);

        $token = new RequestToken($response);
        $this->assertEquals('J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri', $token->oauth_token_secret);
    }

    public function testTokenCastsToEncodedResponseBody()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $token = new RequestToken();
        $token->setToken('jZaee4GF52O3lUb9');
        $token->setTokenSecret('J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri');
        $this->assertEquals($body, (string) $token);
    }

    public function testToStringReturnsEncodedResponseBody()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $token = new RequestToken();
        $token->setToken('jZaee4GF52O3lUb9');
        $token->setTokenSecret('J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri');
        $this->assertEquals($body, $token->toString());
    }

    public function testIsValidDetectsBadResponse()
    {
        $body = 'oauthtoken=jZaee4GF52O3lUb9&oauthtokensecret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $response = new HTTPResponse(200, [], $body);
        $token = new RequestToken($response);
        $this->assertFalse($token->isValid());
    }

    public function testIsValidDetectsGoodResponse()
    {
        $body = 'oauth_token=jZaee4GF52O3lUb9&oauth_token_secret=J4Ms4n8sxjYc0A8K0KOQFCTL0EwUQTri';
        $response = new HTTPResponse;
        $response->setContent($body)
                 ->setStatusCode(200);

        $token = new RequestToken($response);
        $this->assertTrue($token->isValid());
    }
}
