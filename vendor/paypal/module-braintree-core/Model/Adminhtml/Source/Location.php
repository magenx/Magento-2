<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PayPal\Braintree\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Method\AbstractMethod;

class Location implements ArrayInterface
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
                'value' => 'cart',
                'label' => __('Mini-Cart and Cart Page'),
            ],
            [
                'value' => 'checkout',
                'label' => __('Checkout Page'),
            ],
            [
                'value' => 'productpage',
                'label' => __('Product Page')
            ]
        ];
    }
}
