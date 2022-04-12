<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Test\Unit\Gateway\Response;

use Braintree\Transaction;
use PayPal\Braintree\Gateway\Response\ThreeDSecureDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

class ThreeDSecureDetailsHandlerTest extends \PHPUnit\Framework\TestCase
{

    const TRANSACTION_ID = '432er5ww3e';

    /**
     * @var \PayPal\Braintree\Gateway\Response\ThreeDSecureDetailsHandler
     */
    private $handler;

    /**
     * @var \Magento\Sales\Model\Order\Payment|MockObject
     */
    private $payment;

    /**
     * @var SubjectReader|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subjectReaderMock;

    protected function setUp(): void
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'unsAdditionalInformation',
                'hasAdditionalInformation',
                'setAdditionalInformation',
            ])
            ->getMock();

        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->handler = new ThreeDSecureDetailsHandler($this->subjectReaderMock);
    }

    /**
     * @covers \PayPal\Braintree\Gateway\Response\ThreeDSecureDetailsHandler::handle
     */
    public function testHandle()
    {
        $this->markTestSkipped('Skip this test');
        $paymentData = $this->getPaymentDataObjectMock();
        $transaction = $this->getBraintreeTransaction();

        $subject = ['payment' => $paymentData];
        $response = ['object' => $transaction];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);
        $this->subjectReaderMock->expects(self::once())
            ->method('readTransaction')
            ->with($response)
            ->willReturn($transaction);

        $this->payment->expects(static::at(1))
            ->method('setAdditionalInformation')
            ->with('liabilityShifted', 'Yes');
        $this->payment->expects(static::at(2))
            ->method('setAdditionalInformation')
            ->with('liabilityShiftPossible', 'Yes');

        $this->handler->handle($subject, $response);
    }

    /**
     * Create mock for payment data object and order payment
     * @return MockObject
     */
    private function getPaymentDataObjectMock()
    {
        $mock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        return $mock;
    }

    /**
     * Create Braintree transaction
     * @return MockObject
     */
    private function getBraintreeTransaction()
    {
        $attributes = [
            'id' => self::TRANSACTION_ID,
            'threeDSecureInfo' => $this->getThreeDSecureInfo()
        ];

        $transaction = Transaction::factory($attributes);

        return $transaction;
    }

    /**
     * Get 3d secure details
     * @return array
     */
    private function getThreeDSecureInfo()
    {
        $attributes = [
            'liabilityShifted' => 'Yes',
            'liabilityShiftPossible' => 'Yes'
        ];

        return $attributes;
    }
}
