<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model\Adminhtml\Source;

/** @codeCoverageIgnore
 */
class GooglePayCcType extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'VISA', 'label' => 'Visa'],
            ['value' => 'MASTERCARD', 'label' => 'MasterCard'],
            ['value' => 'AMEX', 'label' => 'AMEX'],
            ['value' => 'DISCOVER', 'label' => 'Discover'],
            ['value' => 'JCB', 'label' => 'JCB']
        ];
    }
}
