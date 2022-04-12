<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Config;

use PayPal\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Sales\Model\Order\Payment;

class CanVoidHandler implements ValueHandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * CanVoidHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $subject, $storeId = null)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);
        $canCaptureFlag = true;
        $payment = $paymentDO->getPayment();
        if ((bool)$payment->getAmountPaid()) {
            $canCaptureFlag = false;
        }
        if ($payment->getAmountPaid() < $payment->getAmountAuthorized() && (bool)$payment->getAmountPaid()) {
            $canCaptureFlag = true;
        }
        return $payment instanceof Payment && $canCaptureFlag;
    }
}
