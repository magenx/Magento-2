<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Request;

use InvalidArgumentException;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

class RefundDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     * @param LoggerInterface $logger
     */
    public function __construct(
        SubjectReader $subjectReader,
        LoggerInterface $logger
    ) {
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        $amount = null;

        try {
            $amount = $this->formatPrice($this->subjectReader->readAmount($buildSubject));
        } catch (InvalidArgumentException $e) {
            $this->logger->critical($e->getMessage());
        }

        /**
         * we should remember that Payment sets Capture txn id of current Invoice into ParentTransactionId Field
         * We should also support previous implementations of Magento Braintree -
         * and cut off '-capture' postfix from transaction ID to support backward compatibility
         */
        $txnId = str_replace(
            '-' . TransactionInterface::TYPE_CAPTURE,
            '',
            $payment->getParentTransactionId()
        );

        return [
            'transaction_id' => $txnId,
            PaymentDataBuilder::AMOUNT => $amount
        ];
    }
}
