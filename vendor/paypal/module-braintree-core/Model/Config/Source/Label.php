<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PayPal\Braintree\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Label implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'paypal',
                'label' => __('Paypal'),
            ],
            [
                'value' => 'checkout',
                'label' => __('Checkout'),
            ],
            [
                'value' => 'buynow',
                'label' => __('Buynow'),
            ],
            [
                'value' => 'pay',
                'label' => __('Pay'),
            ]
        ];
    }
}
