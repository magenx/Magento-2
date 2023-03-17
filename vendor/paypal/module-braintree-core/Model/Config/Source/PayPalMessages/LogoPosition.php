<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PayPal\Braintree\Model\Config\Source\PayPalMessages;

use Magento\Framework\Option\ArrayInterface;

class LogoPosition implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'left', 'label' => __('left')],
            ['value' => 'right', 'label' => __('right')],
            ['value' => 'top', 'label' => __('top')]
        ];
    }
}
