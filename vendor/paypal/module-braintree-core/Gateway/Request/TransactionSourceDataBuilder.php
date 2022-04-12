<?php

namespace PayPal\Braintree\Gateway\Request;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;

class TransactionSourceDataBuilder implements BuilderInterface
{
    const TRANSACTION_SOURCE = 'transactionSource';

    /**
     * @var State $state
     */
    private $state;

    /**
     * TransactionSourceDataBuilder constructor
     *
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * Set TRANSACTION_SOURCE to moto if within the admin
     * @inheritdoc
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        if ($this->state->getAreaCode() === Area::AREA_ADMINHTML) {
            return [
                self::TRANSACTION_SOURCE => 'moto'
            ];
        }

        return [];
    }
}
