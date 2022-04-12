<?php

namespace PayPal\Braintree\Api;

use PayPal\Braintree\Api\Data\AuthDataInterface;

/**
 * Interface AuthInterface
 * @api
 **/
interface AuthInterface
{
    /**
     * Returns details required to be able to submit a payment with apple pay.
     * @return \PayPal\Braintree\Api\Data\AuthDataInterface
     */
    public function get(): AuthDataInterface;
}
