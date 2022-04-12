<?php

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
            ['value' => 0, 'label' => __('Blue')],
            ['value' => 1, 'label' => __('Black')],
            ['value' => 2, 'label' => __('Gold')],
            ['value' => 3, 'label' => __('Silver')]
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
            0 => __('Blue'),
            1 => __('Black'),
            2 => __('Gold'),
            3 => __('Silver')
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
            0 => 'blue',
            1 => 'black',
            2 => 'gold',
            3 => 'silver'
        ];
    }
}
