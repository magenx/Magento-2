<?php

namespace PayPal\Braintree\Test\Console;

use Magento\Store\Model\StoreManagerInterface;
use PayPal\Braintree\Console\VaultMigrate;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResourceConnection\ConnectionFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\PaymentTokenFactory;
use Magento\Vault\Test\Block\Onepage\Payment\Method\Vault;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class VaultMigrateTest extends TestCase
{
    /**
     * @var MockObject|ConnectionFactory
     */
    private $connectionFactoryMock;
    /**
     * @var MockObject|BraintreeAdapter
     */
    private $braintreeAdapterMock;
    /**
     * @var MockObject|CustomerRepositoryInterface
     */
    private $customerRepositoryMock;
    /**
     * @var MockObject|PaymentTokenFactory
     */
    private $paymentTokenFactoryMock;
    /**
     * @var MockObject|PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepositoryMock;
    /**
     * @var MockObject|EncryptorInterface
     */
    private $encryptorMock;
    /**
     * @var MockObject|SerializerInterface
     */
    private $jsonMock;
    /**
     * @var MockObject|StoreManagerInterface
     */
    private $storeManagerMock;
    /**
     * @var MockObject|VaultMigrate
     */
    private $command;

    protected function setUp(): void
    {
        $this->connectionFactoryMock = $this->createMock(ConnectionFactory::class);
        $this->braintreeAdapterMock = $this->createMock(BraintreeAdapter::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->paymentTokenFactoryMock = $this->createMock(PaymentTokenFactory::class);
        $this->paymentTokenRepositoryMock = $this->createMock(PaymentTokenRepositoryInterface::class);
        $this->encryptorMock = $this->createMock(EncryptorInterface::class);
        $this->jsonMock = $this->createMock(SerializerInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $this->command = new VaultMigrate(
            $this->connectionFactoryMock,
            $this->braintreeAdapterMock,
            $this->customerRepositoryMock,
            $this->paymentTokenFactoryMock,
            $this->paymentTokenRepositoryMock,
            $this->encryptorMock,
            $this->jsonMock,
            $this->storeManagerMock
        );
    }

    /**
     * @param $customers
     * @dataProvider remapCustomerDataDataProvider
     */
    public function testRemapCustomerData($customers)
    {
        $foo = $this->command->remapCustomerData($customers);
        $this->assertArrayHasKey('braintree_id', $foo[0]);
        $this->assertArrayHasKey('email', $foo[0]);
        $this->assertArrayHasKey('storedCards', $foo[0]);
        $this->assertGreaterThanOrEqual(1, $foo[0]['storedCards']);
    }

    /**
     * @return array
     */
    public function remapCustomerDataDataProvider(): array
    {
        return [
            [
                [
                    (object) [
                        'id' => '886658184',
                        'email' => 'roni_cost@example.com',
                        'creditCards' => [
                            (object) [
                                'token' => '5p7529',
                                'expirationMonth' => '01',
                                'expirationYear' => '2021',
                                'last4' => '1000',
                                'cardType' => 'Visa'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @param $description
     * @dataProvider getOptionListDataProvider
     */
    public function testGetOptionsList($description)
    {
        /* @var \Symfony\Component\Console\Input\InputArgument[] $argsList */
        $argsList = $this->command->getOptionsList();

        $this->assertEquals(VaultMigrate::HOST, $argsList[0]->getName());
        $this->assertEquals($description, $argsList[0]->getDescription());
    }

    /**
     * @return array
     */
    public function getOptionListDataProvider()
    {
        return [
            [
                'description' => 'Hostname/IP. Port is optional'
            ]
        ];
    }
}
