<?php

namespace LaminasTest\OAuth\Signature;

use PHPUnit\Framework\TestCase;

class RSATest extends TestCase
{
    public function testSignatureWithoutAccessSecretIsHashedWithConsumerSecret()
    {
        $this->markTestIncomplete('Laminas\\Crypt\\Rsa finalisation outstanding');
    }

    public function testSignatureWithAccessSecretIsHashedWithConsumerAndAccessSecret()
    {
        $this->markTestIncomplete('Laminas\\Crypt\\Rsa finalisation outstanding');
    }
}
