<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
            ['value' => 'medium', 'label' => __('Medium')],
            ['value' => 'large', 'label' => __('Large')],
            ['value' => 'responsive', 'label' => __('Responsive')]
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
            'medium' => __('Medium'),
            'large' => __('Large'),
            'responsive' => __('Responsive')
        ];
    }

    /**
     * Values in the format needed for the PayPal JS SDK
     * @return array
     */
    public function toRawValues(): array
    {
        return [
            'medium' => 'medium',
            'large' => 'large',
            'responsive' => 'responsive'
        ];
    }
}
