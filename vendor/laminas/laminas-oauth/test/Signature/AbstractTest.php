<?php

namespace LaminasTest\OAuth\Signature;

use Laminas\OAuth\Signature;
use PHPUnit\Framework\TestCase;

class AbstractTest extends TestCase
{
    public function testNormaliseHttpBaseSignatureUrl()
    {
        $sign = new Signature\Plaintext('foo');
        $url = 'HTTP://WWW.EXAMPLE.COM:80/REQUEST';
        $this->assertEquals('http://www.example.com/REQUEST', $sign->normaliseBaseSignatureUrl($url));
    }

    public function testNormaliseHttpsBaseSignatureUrl()
    {
        $sign = new Signature\Plaintext('foo');
        $url = 'HTTPS://WWW.EXAMPLE.COM:443/REQUEST';
        $this->assertEquals('https://www.example.com/REQUEST', $sign->normaliseBaseSignatureUrl($url));
    }

    public function testNormaliseHttpPortBaseSignatureUrl()
    {
        $sign = new Signature\Plaintext('foo');
        $url = 'HTTP://WWW.EXAMPLE.COM:443/REQUEST';
        $this->assertEquals('http://www.example.com:443/REQUEST', $sign->normaliseBaseSignatureUrl($url));
    }

    public function testNormaliseHttpsPortBaseSignatureUrl()
    {
        $sign = new Signature\Plaintext('foo');
        $url = 'HTTPS://WWW.EXAMPLE.COM:80/REQUEST';
        $this->assertEquals('https://www.example.com:80/REQUEST', $sign->normaliseBaseSignatureUrl($url));
    }

    public function testNormaliseHttpsRemovesFragmentFromBaseSignatureUrl()
    {
        $sign = new Signature\Plaintext('foo');
        $url = 'https://www.example.com/request#foo';
        $this->assertEquals('https://www.example.com/request', $sign->normaliseBaseSignatureUrl($url));
    }

    public function testNormaliseHttpsRemovesQueryFromBaseSignatureUrl()
    {
        $sign = new Signature\Plaintext('foo');
        $url = 'https://www.example.com/request?foo=bar';
        $this->assertEquals('https://www.example.com/request', $sign->normaliseBaseSignatureUrl($url));
    }
}
