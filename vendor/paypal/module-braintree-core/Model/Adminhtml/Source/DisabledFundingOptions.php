<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

class DisabledFundingOptions implements ArrayInterface
{
    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'card',
                'label' => __('PayPal Guest Checkout Credit Card Icons'),
            ],
            [
                'value' => 'elv',
                'label' => __('Elektronisches Lastschriftverfahren – German ELV')
            ]
        ];
    }
}
