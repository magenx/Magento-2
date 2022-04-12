<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Ui\Component\Report\Listing\Column;

use Braintree\Transaction;
use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
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

        $statuses = $this->getAvailableStatuses();

        foreach ($statuses as $statusCode => $statusName) {
            $this->options[$statusCode]['label'] = $statusName;
            $this->options[$statusCode]['value'] = $statusCode;
        }

        return $this->options;
    }

    /**
     * @return array
     */
    private function getAvailableStatuses(): array
    {
        // @codingStandardsIgnoreStart
        return [
            Transaction::AUTHORIZATION_EXPIRED => __(Transaction::AUTHORIZATION_EXPIRED),
            Transaction::AUTHORIZING => __(Transaction::AUTHORIZING),
            Transaction::AUTHORIZED => __(Transaction::AUTHORIZED),
            Transaction::GATEWAY_REJECTED => __(Transaction::GATEWAY_REJECTED),
            Transaction::FAILED => __(Transaction::FAILED),
            Transaction::PROCESSOR_DECLINED => __(Transaction::PROCESSOR_DECLINED),
            Transaction::SETTLED => __(Transaction::SETTLED),
            Transaction::SETTLING => __(Transaction::SETTLING),
            Transaction::SUBMITTED_FOR_SETTLEMENT => __(Transaction::SUBMITTED_FOR_SETTLEMENT),
            Transaction::VOIDED => __(Transaction::VOIDED),
            Transaction::UNRECOGNIZED => __(Transaction::UNRECOGNIZED),
            Transaction::SETTLEMENT_DECLINED => __(Transaction::SETTLEMENT_DECLINED),
            Transaction::SETTLEMENT_PENDING => __(Transaction::SETTLEMENT_PENDING),
            Transaction::SETTLEMENT_CONFIRMED => __(Transaction::SETTLEMENT_CONFIRMED)
        ];
        // @codingStandardsIgnoreEnd
    }
}
