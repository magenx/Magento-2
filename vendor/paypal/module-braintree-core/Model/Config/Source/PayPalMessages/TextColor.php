<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PayPal\Braintree\Model\Config\Source\PayPalMessages;

use Magento\Framework\Option\ArrayInterface;

class TextColor implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'black', 'label' => __('black')],
            ['value' => 'white', 'label' => __('white')],
            ['value' => 'monochrome', 'label' => __('monochrome')],
            ['value' => 'grayscale', 'label' => __('grayscale')]
        ];
    }
}
