<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Test\Unit\Model;

use PayPal\Braintree\Model\AvsEmsCodeMapper;
use PayPal\Braintree\Model\Ui\ConfigProvider;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

class AvsEmsCodeMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AvsEmsCodeMapper
     */
    private $mapper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->mapper = new AvsEmsCodeMapper();
    }

    /**
     * Checks different variations for AVS codes mapping.
     *
     * @covers \PayPal\Braintree\Model\AvsEmsCodeMapper::getCode
     * @param string $avsZip
     * @param string $avsStreet
     * @param string $expected
     * @dataProvider getCodeDataProvider
     */
    public function testGetCode($avsZip, $avsStreet, $expected)
    {
        /** @var OrderPaymentInterface|MockObject $orderPayment */
        $orderPayment = $this->getMockBuilder(OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $orderPayment->expects(self::once())
            ->method('getMethod')
            ->willReturn(ConfigProvider::CODE);

        $orderPayment->expects(self::once())
            ->method('getAdditionalInformation')
            ->willReturn([
                'avsPostalCodeResponseCode' => $avsZip,
                'avsStreetAddressResponseCode' => $avsStreet
            ]);

        self::assertEquals($expected, $this->mapper->getCode($orderPayment));
    }

    /**
     * Checks a test case, when payment order is not Braintree payment method.
     *
     * @covers \PayPal\Braintree\Model\AvsEmsCodeMapper::getCode
     */
    public function testGetCodeWithException()
    {
        $this->markTestSkipped('Skip this test');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "some_payment" does not supported by Braintree AVS mapper.');

        /** @var OrderPaymentInterface|MockObject $orderPayment */
        $orderPayment = $this->getMockBuilder(OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $orderPayment->expects(self::exactly(2))
            ->method('getMethod')
            ->willReturn('some_payment');

        $this->mapper->getCode($orderPayment);
    }

    /**
     * Gets list of AVS codes.
     *
     * @return array
     */
    public function getCodeDataProvider()
    {
        return [
            ['avsZip' => null, 'avsStreet' => null, 'expected' => 'U'],
            ['avsZip' => null, 'avsStreet' => 'M', 'expected' => 'U'],
            ['avsZip' => 'M', 'avsStreet' => null, 'expected' => 'U'],
            ['avsZip' => 'M', 'avsStreet' => 'Unknown', 'expected' => 'U'],
            ['avsZip' => 'I', 'avsStreet' => 'A', 'expected' => 'U'],
            ['avsZip' => 'M', 'avsStreet' => 'M', 'expected' => 'Y'],
            ['avsZip' => 'N', 'avsStreet' => 'M', 'expected' => 'A'],
            ['avsZip' => 'M', 'avsStreet' => 'N', 'expected' => 'Z'],
            ['avsZip' => 'N', 'avsStreet' => 'N', 'expected' => 'N'],
            ['avsZip' => 'U', 'avsStreet' => 'U', 'expected' => 'U'],
            ['avsZip' => 'I', 'avsStreet' => 'I', 'expected' => 'U'],
            ['avsZip' => 'A', 'avsStreet' => 'A', 'expected' => 'E'],
        ];
    }
}
