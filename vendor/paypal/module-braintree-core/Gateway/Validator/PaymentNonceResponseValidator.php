<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Validator;

class PaymentNonceResponseValidator extends GeneralResponseValidator
{
    /**
     * Get response validators for payment nonce
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
                        !empty($response->paymentMethodNonce) && !empty($response->paymentMethodNonce->nonce),
                        [__('Payment method nonce can\'t be retrieved.')]
                    ];
                }
            ]
        );
    }
}
