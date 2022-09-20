<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use PayPal\Braintree\Observer\DataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class DataAssignObserverTest extends \PHPUnit\Framework\TestCase
{
    private const PAYMENT_METHOD_NONCE = 'nonce';
    private const DEVICE_DATA = '{"test": "test"}';

    public function testExecute()
    {
        $this->markTestSkipped('Skip this test');
        $observerContainer = $this->getMockBuilder(Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentInfoModel = $this->createMock(InfoInterface::class);
        $dataObject = new DataObject(
            [
                PaymentInterface::KEY_ADDITIONAL_DATA => [
                    'payment_method_nonce' => self::PAYMENT_METHOD_NONCE,
                    'device_data' => self::DEVICE_DATA
                ]
            ]
        );
        $observerContainer->expects(static::atLeastOnce())
            ->method('getEvent')
            ->willReturn($event);
        $event->expects(static::exactly(2))
            ->method('getDataByKey')
            ->willReturnMap(
                [
                    [AbstractDataAssignObserver::MODEL_CODE, $paymentInfoModel],
                    [AbstractDataAssignObserver::DATA_CODE, $dataObject]
                ]
            );
        $paymentInfoModel->expects(static::at(0))
            ->method('setAdditionalInformation')
            ->with('payment_method_nonce', self::PAYMENT_METHOD_NONCE);
        $paymentInfoModel->expects(static::at(1))
            ->method('setAdditionalInformation')
            ->with('device_data', self::DEVICE_DATA);

        $observer = new DataAssignObserver();
        $observer->execute($observerContainer);
    }
}
