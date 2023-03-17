<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Response;

use Braintree\Transaction;
use PayPal\Braintree\Observer\DataAssignObserver;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class PaymentDetailsHandler implements HandlerInterface
{
    public const AVS_POSTAL_RESPONSE_CODE = 'avsPostalCodeResponseCode';
    public const AVS_STREET_ADDRESS_RESPONSE_CODE = 'avsStreetAddressResponseCode';
    public const CVV_RESPONSE_CODE = 'cvvResponseCode';
    public const PROCESSOR_AUTHORIZATION_CODE = 'processorAuthorizationCode';
    public const PROCESSOR_RESPONSE_CODE = 'processorResponseCode';
    public const PROCESSOR_RESPONSE_TEXT = 'processorResponseText';
    public const TRANSACTION_SOURCE = 'transactionSource';

    /**
     * List of additional details
     *
     * @var array
     */
    protected $additionalInformationMapping = [
        self::AVS_POSTAL_RESPONSE_CODE,
        self::AVS_STREET_ADDRESS_RESPONSE_CODE,
        self::CVV_RESPONSE_CODE,
        self::PROCESSOR_AUTHORIZATION_CODE,
        self::PROCESSOR_RESPONSE_CODE,
        self::PROCESSOR_RESPONSE_TEXT,
    ];

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var State
     */
    private $state;

    /**
     * PaymentDetailsHandler Constructor
     *
     * @param SubjectReader $subjectReader
     * @param State $state
     */
    public function __construct(
        SubjectReader $subjectReader,
        State $state
    ) {
        $this->subjectReader = $subjectReader;
        $this->state = $state;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        /** @var Transaction $transaction */
        $transaction = $this->subjectReader->readTransaction($response);
        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();

        $payment->setCcTransId($transaction->id);
        $payment->setLastTransId($transaction->id);

        //remove previously set payment nonce
        $payment->unsAdditionalInformation(DataAssignObserver::PAYMENT_METHOD_NONCE);
        foreach ($this->additionalInformationMapping as $item) {
            if (!isset($transaction->$item)) {
                continue;
            }
            $payment->setAdditionalInformation($item, $transaction->$item);
        }

        $this->setTransactionSource($payment);
    }

    /**
     * When within admin area; assume MOTO transactionSource
     *
     * @param OrderPaymentInterface $payment
     * @throws LocalizedException
     * @throws LocalizedException
     */
    public function setTransactionSource(OrderPaymentInterface $payment)
    {
        if ($this->state->getAreaCode() === Area::AREA_ADMINHTML) {
            $payment->setAdditionalInformation(self::TRANSACTION_SOURCE, 'MOTO');
        }
    }
}
