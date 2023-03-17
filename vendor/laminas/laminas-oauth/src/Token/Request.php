<?php

namespace Laminas\OAuth\Token;

use Laminas\Http\Response as HTTPResponse;
use Laminas\OAuth\Client;
use Laminas\OAuth\Http\Utility as HTTPUtility;

/**
 * @category   Laminas
 * @package    Laminas_OAuth
 */
class Request extends AbstractToken
{
    /**
     * Constructor
     *
     * @param null|Laminas\Http\Response $response
     * @param null|Laminas\OAuth\Http\Utility $utility
     */
    public function __construct(
        HTTPResponse $response = null,
        HTTPUtility $utility = null
    ) {
        parent::__construct($response, $utility);

        // detect if server supports OAuth 1.0a
        if (isset($this->_params[AbstractToken::TOKEN_PARAM_CALLBACK_CONFIRMED])) {
            Client::$supportsRevisionA = true;
        }
    }
}
