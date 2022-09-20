<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class SettlementDataBuilder implements BuilderInterface
{
    public const SUBMIT_FOR_SETTLEMENT = 'submitForSettlement';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        return [
            'options' => [
                self::SUBMIT_FOR_SETTLEMENT => true
            ]
        ];
    }
}
