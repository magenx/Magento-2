<?php

namespace Laminas\OAuth\Token;

use Laminas\Http\Response as HTTPResponse;
use Laminas\OAuth\Http\Utility as HTTPUtility;

interface TokenInterface
{

    /**
     * Retrieve an arbitrary named parameter from the token
     *
     * @param  string $name
     * @return mixed
     */
    public function getParam($name);

    /**
     * Retrieve the response object this token is operating on
     *
     * @return HTTPResponse
     */
    public function getResponse();

    /**
     * Retrieve the token value
     *
     * @return string
     */
    public function getToken();

    /**
     * Retrieve the Token's secret, for use with signing requests
     *
     * @return string
     */
    public function getTokenSecret();

    /**
     * Set the Token's signing secret.
     *
     * @param  string $secret
     * @return Laminas\OAuth\Token
     */
    public function setTokenSecret($secret);

    /**
     * Validate the Token against the HTTP Response
     *
     * @return boolean
     */
    public function isValid();

    /**
     * Convert token to a raw-encoded query string
     *
     * @return string
     */
    public function toString();

    /**
     * Cast Token to string representation; should proxy to toString()
     *
     * @return string
     */
    public function __toString();
}
