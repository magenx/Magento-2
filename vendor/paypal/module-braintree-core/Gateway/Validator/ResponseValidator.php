<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Validator;

use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Transaction;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class ResponseValidator extends GeneralResponseValidator
{
    /**
     * Get response validators
     *
     * @return array
     */
    protected function getResponseValidators(): array
    {
        return array_merge(
            parent::getResponseValidators(),
            [
                static function ($response) {
                    return [
                        $response instanceof Successful
                        && isset($response->transaction)
                        && in_array(
                            $response->transaction->status,
                            [
                                Transaction::AUTHORIZED,
                                Transaction::SUBMITTED_FOR_SETTLEMENT,
                                Transaction::SETTLING,
                                Transaction::SETTLEMENT_PENDING
                            ],
                            true
                        ),
                        [__('Wrong transaction status')]
                    ];
                }
            ]
        );
    }
}
