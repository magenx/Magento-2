<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Braintree\Model\Lpm;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use PayPal\Braintree\Gateway\Response\PaymentDetailsHandler as BraintreePaymentDetailsHandler;
use PayPal\Braintree\Observer\DataAssignObserver;

class PaymentDetailsHandler implements HandlerInterface
{
    private const PAYPAL_PAYMENT_ID = 'paymentId';
    private const FUNDING_SOURCE = 'fundingSource';

    /**
     * @var array
     */
    private array $additionalInformationMapping = [
        BraintreePaymentDetailsHandler::PROCESSOR_AUTHORIZATION_CODE,
        BraintreePaymentDetailsHandler::PROCESSOR_RESPONSE_CODE,
        BraintreePaymentDetailsHandler::PROCESSOR_RESPONSE_TEXT,
    ];

    /**
     * @var SubjectReader
     */
    private SubjectReader $subjectReader;

    /**
     * PaymentDetailsHandler Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $transaction = $this->subjectReader->readTransaction($response);

        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();

        $payment->unsAdditionalInformation(DataAssignObserver::PAYMENT_METHOD_NONCE);
        foreach ($this->additionalInformationMapping as $item) {
            if (!isset($transaction->$item)) {
                continue;
            }
            $payment->setAdditionalInformation($item, $transaction->$item);
        }

        // Set PayPal paymentId and fundingSource of Local Payments
        $localPayment = $this->subjectReader->readLocalPayment($transaction);
        $payment->setAdditionalInformation(self::PAYPAL_PAYMENT_ID, $localPayment[self::PAYPAL_PAYMENT_ID]);
        $payment->setAdditionalInformation(self::FUNDING_SOURCE, $localPayment[self::FUNDING_SOURCE]);
    }
}
