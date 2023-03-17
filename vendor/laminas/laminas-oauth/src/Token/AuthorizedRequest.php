<?php

namespace Laminas\OAuth\Token;

use Laminas\OAuth\Http;

/**
 * @category   Laminas
 * @package    Laminas_OAuth
 */
class AuthorizedRequest extends AbstractToken
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @param  null|array $data
     * @param  null|\Laminas\OAuth\Http\Utility $utility
     * @return void
     */
    public function __construct(array $data = null, Http\Utility $utility = null)
    {
        if ($data !== null) {
            $this->data = $data;
            $params = $this->parseData();
            if (count($params) > 0) {
                $this->setParams($params);
            }
        }
        if ($utility !== null) {
            $this->httpUtility = $utility;
        } else {
            $this->httpUtility = new Http\Utility;
        }
    }

    /**
     * Retrieve token data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Indicate if token is valid
     *
     * @return bool
     */
    public function isValid()
    {
        if (isset($this->params[self::TOKEN_PARAM_KEY])
            && ! empty($this->params[self::TOKEN_PARAM_KEY])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Parse string data into array
     *
     * @return array
     */
    protected function parseData()
    {
        $params = [];
        if (empty($this->data)) {
            return;
        }
        foreach ($this->data as $key => $value) {
            $params[rawurldecode($key)] = rawurldecode($value);
        }
        return $params;
    }
}
