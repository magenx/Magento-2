<?php

namespace Laminas\OAuth\Signature;

use Laminas\OAuth\Exception;
use Laminas\OAuth\Http\Utility as HTTPUtility;
use Laminas\Uri;

/**
 * @category   Laminas
 * @package    Laminas_OAuth
 */
abstract class AbstractSignature implements SignatureInterface
{
    /**
     * Hash algorithm to use when generating signature
     * @var string
     */
    protected $hashAlgorithm = null;

    /**
     * Key to use when signing
     * @var string
     */
    protected $key = null;

    /**
     * Consumer secret
     * @var string
     */
    protected $consumerSecret = null;

    /**
     * Token secret
     * @var string
     */
    protected $tokenSecret = '';

    /**
     * Constructor
     *
     * @param  string $consumerSecret
     * @param  null|string $tokenSecret
     * @param  null|string $hashAlgo
     * @return void
     */
    public function __construct($consumerSecret, $tokenSecret = null, $hashAlgo = null)
    {
        $this->consumerSecret = $consumerSecret;
        if (isset($tokenSecret)) {
            $this->tokenSecret = $tokenSecret;
        }
        $this->key = $this->assembleKey();
        if (isset($hashAlgo)) {
            $this->hashAlgorithm = $hashAlgo;
        }
    }

    /**
     * Normalize the base signature URL
     *
     * @param  string $url
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function normaliseBaseSignatureUrl($url)
    {
        $uri = Uri\UriFactory::factory($url);
        $uri->normalize();
        if ($uri->getScheme() == 'http' && $uri->getPort() == '80') {
            $uri->setPort('');
        } elseif ($uri->getScheme() == 'https' && $uri->getPort() == '443') {
            $uri->setPort('');
        } elseif (! in_array($uri->getScheme(), ['http', 'https'])) {
            throw new Exception\InvalidArgumentException('Invalid URL provided; must be an HTTP or HTTPS scheme');
        }
        $uri->setQuery('');
        $uri->setFragment('');
        return $uri->toString();
    }

    /**
     * Assemble key from consumer and token secrets
     *
     * @return string
     */
    protected function assembleKey()
    {
        $parts = [$this->consumerSecret];
        if ($this->tokenSecret !== null) {
            $parts[] = $this->tokenSecret;
        }
        foreach ($parts as $key => $secret) {
            $parts[$key] = HTTPUtility::urlEncode($secret);
        }
        return implode('&', $parts);
    }

    /**
     * Get base signature string
     *
     * @param  array $params
     * @param  null|string $method
     * @param  null|string $url
     * @return string
     */
    protected function getBaseSignatureString(array $params, $method = null, $url = null)
    {
        $encodedParams = [];
        foreach ($params as $key => $value) {
            $encodedParams[HTTPUtility::urlEncode($key)] =
                HTTPUtility::urlEncode($value);
        }
        $baseStrings = [];
        if (isset($method)) {
            $baseStrings[] = strtoupper($method);
        }
        if (isset($url)) {
            // should normalise later
            $baseStrings[] = HTTPUtility::urlEncode(
                $this->normaliseBaseSignatureUrl($url)
            );
        }
        if (isset($encodedParams['oauth_signature'])) {
            unset($encodedParams['oauth_signature']);
        }
        $baseStrings[] = HTTPUtility::urlEncode(
            $this->toByteValueOrderedQueryString($encodedParams)
        );
        return implode('&', $baseStrings);
    }

    /**
     * Transform an array to a byte value ordered query string
     *
     * @param  array $params
     * @return string
     */
    protected function toByteValueOrderedQueryString(array $params)
    {
        $return = [];
        uksort($params, 'strnatcmp');
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                natsort($value);
                foreach ($value as $keyduplicate) {
                    $return[] = $key . '=' . $keyduplicate;
                }
            } else {
                $return[] = $key . '=' . $value;
            }
        }
        return implode('&', $return);
    }
}
