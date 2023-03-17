<?php

namespace Laminas\OAuth\Signature;

/**
 * @category   Laminas
 * @package    Laminas_OAuth
 */
class Plaintext extends AbstractSignature
{
    /**
     * Sign a request
     *
     * @param  array $params
     * @param  null|string $method
     * @param  null|string $url
     * @return string
     */
    public function sign(array $params, $method = null, $url = null)
    {
        if ($this->tokenSecret === null) {
            return $this->consumerSecret . '&';
        }
        $return = implode('&', [$this->consumerSecret, $this->tokenSecret]);
        return $return;
    }
}
