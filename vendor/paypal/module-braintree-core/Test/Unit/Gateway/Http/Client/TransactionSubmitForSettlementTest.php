<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Test\Unit\Gateway\Http\Client;

use Braintree\Result\Successful;
use PayPal\Braintree\Gateway\Http\Client\TransactionSubmitForSettlement;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class TransactionSubmitForSettlementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TransactionSubmitForSettlement
     */
    private TransactionSubmitForSettlement $client;

    /**
     * @var Logger|MockObject
     */
    private Logger|MockObject $logger;

    /**
     * @var BraintreeAdapter|MockObject
     */
    private BraintreeAdapter|MockObject $adapter;

    protected function setUp(): void
    {
        $criticalLoggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['debug'])
            ->getMock();
        $this->adapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['submitForSettlement'])
            ->getMock();

        $this->client = new TransactionSubmitForSettlement(
            $criticalLoggerMock,
            $this->logger,
            $this->adapter
        );
    }

    /**
     * @covers \PayPal\Braintree\Gateway\Http\Client\TransactionSubmitForSettlement::placeRequest
     */
    public function testPlaceRequestWithException()
    {
        $this->markTestSkipped('Skip this test');
        $this->expectException(\Magento\Payment\Gateway\Http\ClientException::class);
        $this->expectExceptionMessage('Transaction has been declined');

        $exception = new \Exception('Transaction has been declined');
        $this->adapter->expects(static::once())
            ->method('submitForSettlement')
            ->willThrowException($exception);

        /** @var TransferInterface|MockObject $transferObjectMock */
        $transferObjectMock = $this->getTransferObjectMock();
        $this->client->placeRequest($transferObjectMock);
    }

    /**
     * @covers \PayPal\Braintree\Gateway\Http\Client\TransactionSubmitForSettlement::process
     */
    public function testPlaceRequest()
    {
        $data = new Successful(['success'], [true]);
        $this->adapter->expects(static::once())
            ->method('submitForSettlement')
            ->willReturn($data);

        /** @var TransferInterface|MockObject $transferObjectMock */
        $transferObjectMock = $this->getTransferObjectMock();
        $response = $this->client->placeRequest($transferObjectMock);
        static::assertIsObject($response['object']);
        static::assertEquals(['object' => $data], $response);
    }

    /**
     * @return TransferInterface|MockObject
     */
    private function getTransferObjectMock()
    {
        $mock = $this->createMock(TransferInterface::class);
        $mock->expects($this->once())
            ->method('getBody')
            ->willReturn([
                'transaction_id' => 'vb4c6b',
                'amount' => 124.00,
                'orderId' => '000000002'
            ]);

        return $mock;
    }
}
