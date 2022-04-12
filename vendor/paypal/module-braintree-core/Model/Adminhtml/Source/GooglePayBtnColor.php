<?php

namespace PayPal\Braintree\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

class GooglePayBtnColor implements ArrayInterface
{
    const OPTION_WHITE = 0;
    const OPTION_BLACK = 1;

    public function toOptionArray()
    {
        return [
            [
                'value' => self::OPTION_WHITE,
                'label' => 'White'
            ],
            [
                'value' => self::OPTION_BLACK,
                'label' => 'Black',
            ]
        ];
    }
}
