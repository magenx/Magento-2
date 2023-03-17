<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Helper;

use Braintree\Transaction;
use InvalidArgumentException;
use Magento\Quote\Model\Quote;
use Magento\Payment\Gateway\Helper;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class SubjectReader
{
    /**
     * Reads response object from subject
     *
     * @param array $subject
     * @return object
     */
    public function readResponseObject(array $subject)
    {
        $response = Helper\SubjectReader::readResponse($subject);
        if (!isset($response['object']) || !is_object($response['object'])) {
            throw new InvalidArgumentException('Response object does not exist');
        }

        return $response['object'];
    }

    /**
     * Reads payment from subject
     *
     * @param array $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject): PaymentDataObjectInterface
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * Reads transaction from subject
     *
     * @param array $subject
     * @return Transaction
     */
    public function readTransaction(array $subject): Transaction
    {
        if (!isset($subject['object']) || !is_object($subject['object'])) {
            throw new InvalidArgumentException('Response object does not exist');
        }

        if (!isset($subject['object']->transaction)
            && !$subject['object']->transaction instanceof Transaction
        ) {
            throw new InvalidArgumentException('The object is not a class \Braintree\Transaction.');
        }

        return $subject['object']->transaction;
    }

    /**
     * Reads amount from subject
     *
     * @param array $subject
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        return Helper\SubjectReader::readAmount($subject);
    }

    /**
     * Reads customer id from subject
     *
     * @param array $subject
     * @return int
     */
    public function readCustomerId(array $subject): int
    {
        if (!isset($subject['customer_id'])) {
            throw new InvalidArgumentException('The "customerId" field does not exists');
        }

        return (int) $subject['customer_id'];
    }

    /**
     * Reads public hash from subject
     *
     * @param array $subject
     * @return string
     */
    public function readPublicHash(array $subject): string
    {
        if (empty($subject[PaymentTokenInterface::PUBLIC_HASH])) {
            throw new InvalidArgumentException('The "public_hash" field does not exists');
        }

        return $subject[PaymentTokenInterface::PUBLIC_HASH];
    }

    /**
     * Reads PayPal details from transaction object
     *
     * @param Transaction $transaction
     * @return array
     */
    public function readPayPal(Transaction $transaction): array
    {
        if (!isset($transaction->paypal)) {
            throw new InvalidArgumentException(__('Transaction has not paypal attribute'));
        }

        return $transaction->paypal;
    }

    /**
     * Reads Local Payment details from transaction object
     *
     * @param Transaction $transaction
     * @return array
     */
    public function readLocalPayment(Transaction $transaction): array
    {
        if (!isset($transaction->localPayment)) {
            throw new InvalidArgumentException(__('Transaction has not localPayment attribute'));
        }

        return $transaction->localPayment;
    }
}
