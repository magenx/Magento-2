<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Response;

use Braintree\Transaction;
use Magento\Sales\Model\Order\Payment;

class VoidHandler extends TransactionIdHandler
{
    /**
     * Set transaction Id
     *
     * @param Payment $orderPayment
     * @param Transaction $transaction
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function setTransactionId(Payment $orderPayment, Transaction $transaction)
    {
        return null;
    }

    /**
     * Whether transaction should be closed
     *
     * @return bool
     */
    protected function shouldCloseTransaction(): bool
    {
        return true;
    }

    /**
     * Whether parent transaction should be closed
     *
     * @param Payment $orderPayment
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment): bool
    {
        return true;
    }
}
