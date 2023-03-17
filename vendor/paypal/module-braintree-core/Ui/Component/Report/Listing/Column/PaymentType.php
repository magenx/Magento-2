<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Ui\Component\Report\Listing\Column;

use Braintree\PaymentInstrumentType;
use Magento\Framework\Data\OptionSourceInterface;

class PaymentType implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $types = $this->getAvailablePaymentTypes();

        foreach ($types as $typeCode => $typeName) {
            $this->options[$typeCode]['label'] = $typeName;
            $this->options[$typeCode]['value'] = $typeCode;
        }

        return $this->options;
    }

    /**
     * @return array
     */
    private function getAvailablePaymentTypes(): array
    {
        // @codingStandardsIgnoreStart
        return [
            PaymentInstrumentType::PAYPAL_ACCOUNT => __(PaymentInstrumentType::PAYPAL_ACCOUNT),
            PaymentInstrumentType::CREDIT_CARD => __(PaymentInstrumentType::CREDIT_CARD),
            PaymentInstrumentType::APPLE_PAY_CARD => __(PaymentInstrumentType::APPLE_PAY_CARD),
            PaymentInstrumentType::GOOGLE_PAY_CARD => __(PaymentInstrumentType::GOOGLE_PAY_CARD)
        ];
        // @codingStandardsIgnoreEnd
    }
}
