<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PayPal\Braintree\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Color implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'blue', 'label' => __('Blue')],
            ['value' => 'black', 'label' => __('Black')],
            ['value' => 'gold', 'label' => __('Gold')],
            ['value' => 'silver', 'label' => __('Silver')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'blue' => __('Blue'),
            'black' => __('Black'),
            'gold' => __('Gold'),
            'silver' => __('Silver')
        ];
    }

    /**
     * Values in the format needed for the PayPal JS SDK
     *
     * @return array
     */
    public function toRawValues(): array
    {
        return [
            'blue' => 'blue',
            'black' => 'black',
            'gold' => 'gold',
            'silver' => 'silver'
        ];
    }
}
