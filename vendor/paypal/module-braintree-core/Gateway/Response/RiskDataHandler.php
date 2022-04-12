<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Response;

use Braintree\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\ContextHelper;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class RiskDataHandler implements HandlerInterface
{
    const RISK_DATA_ID = 'riskDataId';

    /**
     * The possible values of the risk decision are Not Evaluated, Approve, Review, and Decline
     */
    const RISK_DATA_DECISION = 'riskDataDecision';

    /**
     * Risk data Review status
     */
    private static $statusReview = 'Review';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        /** @var Transaction $transaction */
        $transaction = $this->subjectReader->readTransaction($response);

        if (!isset($transaction->riskData)) {
            return;
        }

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $payment->setAdditionalInformation(self::RISK_DATA_ID, $transaction->riskData->id);
        $payment->setAdditionalInformation(self::RISK_DATA_DECISION, $transaction->riskData->decision);

        // Mark payment as fraud
        if ($transaction->riskData->decision === self::$statusReview) {
            // We have to set the transaction to pending, so it is not captured right away.
            $payment->setIsTransactionPending(true);
            $payment->setIsFraudDetected(true);
        }
    }
}
