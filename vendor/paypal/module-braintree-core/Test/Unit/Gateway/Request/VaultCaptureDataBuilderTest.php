<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Test\Unit\Gateway\Request;

use PayPal\Braintree\Gateway\Helper\SubjectReader;
use PayPal\Braintree\Gateway\Request\VaultCaptureDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Model\PaymentToken;

class VaultCaptureDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var VaultCaptureDataBuilder
     */
    private $builder;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentDO;

    /**
     * @var Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $payment;

    /**
     * @var SubjectReader|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subjectReader;

    protected function setUp(): void
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new VaultCaptureDataBuilder($this->subjectReader);
    }

    /**
     * \PayPal\Braintree\Gateway\Request\VaultCaptureDataBuilder::build
     */
    public function testBuild()
    {
        $amount = 30.00;
        $token = '5tfm4c';
        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => $amount
        ];

        $expected = [
            'amount' => $amount,
            'paymentMethodToken' => $token
        ];

        $this->subjectReader->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDO);
        $this->subjectReader->expects(self::once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willReturn($amount);

        $paymentExtension = $this->getMockBuilder(OrderPaymentExtension::class)
            ->setMethods(['getVaultPaymentToken'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $paymentToken = $this->getMockBuilder(PaymentToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentExtension->expects(static::once())
            ->method('getVaultPaymentToken')
            ->willReturn($paymentToken);
        $this->payment->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($paymentExtension);

        $paymentToken->expects(static::once())
            ->method('getGatewayToken')
            ->willReturn($token);

        $result = $this->builder->build($buildSubject);
        static::assertEquals($expected, $result);
    }
}
