<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PayPal\Braintree\Model\Config\Source\PayPalMessages;

use Magento\Framework\Option\ArrayInterface;

class Logo implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'inline', 'label' => __('inline')],
            ['value' => 'primary', 'label' => __('primary')],
            ['value' => 'alternative', 'label' => __('alternative')],
            ['value' => 'none', 'label' => __('none')]
        ];
    }
}
