<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use PayPal\Braintree\Gateway\Request\CaptureDataBuilder;
use PHPUnit\Framework\MockObject\MockObject;

class CaptureDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CaptureDataBuilder
     */
    private $builder;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var Payment|MockObject
     */
    private $paymentDO;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    private $order;

    protected function setUp(): void
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->order = $this->getMockBuilder(OrderAdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new CaptureDataBuilder($this->subjectReaderMock);
    }

    /**
     * @covers \PayPal\Braintree\Gateway\Request\CaptureDataBuilder::build
     */
    public function testBuildWithException()
    {
        $this->markTestSkipped('Skip this test');
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('No authorization transaction to proceed capture.');

        $amount = 10.00;
        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => $amount
        ];

        $this->payment->expects(static::once())
            ->method('getCcTransId')
            ->willReturn('');

        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDO);

        $this->builder->build($buildSubject);
    }

    /**
     * @covers \PayPal\Braintree\Gateway\Request\CaptureDataBuilder::build
     */
    public function testBuild()
    {
        $transactionId = 'b3b99d';
        $amount = 10.00;
        $orderId = '000000002';

        $expected = [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'orderId' => $orderId
        ];

        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => $amount
        ];

        $this->payment->expects(static::once())
            ->method('getCcTransId')
            ->willReturn($transactionId);

        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDO);
        $this->subjectReaderMock->expects(self::once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willReturn($amount);

        $this->paymentDO->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->order);

        $this->order->expects(static::once())
            ->method('getOrderIncrementId')
            ->willReturn($orderId);

        static::assertEquals($expected, $this->builder->build($buildSubject));
    }
}
