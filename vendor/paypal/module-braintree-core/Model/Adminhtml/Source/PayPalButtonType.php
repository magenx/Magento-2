<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PayPal\Braintree\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Method\AbstractMethod;

class PayPalButtonType implements ArrayInterface
{
    /**
     * Possible actions on order place
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'paypal',
                'label' => __('PayPal Button'),
            ],
            [
                'value' => 'paylater',
                'label' => __('PayPal Pay Later Button'),
            ],
            [
                'value' => 'credit',
                'label' => __('PayPal Credit Button')
            ]
        ];
    }
}
