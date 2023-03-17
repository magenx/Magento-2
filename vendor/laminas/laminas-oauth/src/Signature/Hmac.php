<?php

namespace Laminas\OAuth\Signature;

use Laminas\Crypt\Hmac as HMACEncryption;

/**
 * @category   Laminas
 * @package    Laminas_OAuth
 */
class Hmac extends AbstractSignature
{
    /**
     * Sign a request
     *
     * @param  array $params
     * @param  mixed $method
     * @param  mixed $url
     * @return string
     */
    public function sign(array $params, $method = null, $url = null)
    {
        $binaryHash = HMACEncryption::compute(
            $this->key,
            $this->hashAlgorithm,
            $this->getBaseSignatureString($params, $method, $url),
            HMACEncryption::OUTPUT_BINARY
        );
        return base64_encode($binaryHash);
    }
}
