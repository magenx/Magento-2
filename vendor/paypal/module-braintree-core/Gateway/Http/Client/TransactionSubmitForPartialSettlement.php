<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Http\Client;

use PayPal\Braintree\Gateway\Request\CaptureDataBuilder;
use PayPal\Braintree\Gateway\Request\PaymentDataBuilder;

class TransactionSubmitForPartialSettlement extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        return  $this->adapter->submitForPartialSettlement(
            $data[CaptureDataBuilder::TRANSACTION_ID],
            $data[PaymentDataBuilder::AMOUNT],
            [PaymentDataBuilder::ORDER_ID => $data[PaymentDataBuilder::ORDER_ID]]
        );
    }
}
