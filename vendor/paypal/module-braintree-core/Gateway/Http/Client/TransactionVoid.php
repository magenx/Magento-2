<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Http\Client;

use Braintree\Result\Error;
use Braintree\Result\Successful;

class TransactionVoid extends AbstractTransaction
{
    /**
     * Process http request
     *
     * @param array $data
     * @return Error|Successful
     */
    protected function process(array $data)
    {
        return $this->adapter->void($data['transaction_id']);
    }
}
