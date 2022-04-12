<?php

namespace PayPal\Braintree\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Size implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 0, 'label' => __('Medium')],
            ['value' => 1, 'label' => __('Large')],
            ['value' => 2, 'label' => __('Responsive')]
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
            0 => __('Medium'),
            1 => __('Large'),
            2 => __('Responsive')
        ];
    }

    /**
     * Values in the format needed for the PayPal JS SDK
     * @return array
     */
    public function toRawValues(): array
    {
        return [
            0 => 'medium',
            1 => 'large',
            2 => 'responsive'
        ];
    }
}
