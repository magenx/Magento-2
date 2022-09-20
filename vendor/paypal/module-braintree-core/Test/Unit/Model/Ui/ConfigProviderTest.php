<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Test\Unit\Model\Ui;

use PayPal\Braintree\Gateway\Config\Config;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use PayPal\Braintree\Model\Ui\ConfigProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use PayPal\Braintree\Gateway\Config\PayPal\Config as PayPalConfig;
use Magento\Payment\Model\CcConfig;
use Magento\Framework\View\Asset\Source;

/**
 * Test for class \PayPal\Braintree\Model\Ui\ConfigProvider
 */
class ConfigProviderTest extends TestCase
{
    const SDK_URL = 'https://js.braintreegateway.com/v2/braintree.js';
    const CLIENT_TOKEN = 'token';
    const MERCHANT_ACCOUNT_ID = '245345';

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var BraintreeAdapter|MockObject
     */
    private $braintreeAdapter;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var PayPalConfig|MockObject
     */
    private $payPalConfig;

    /**
     * @var CcConfig
     */
    private $ccConfig;

    /**
     * @var Source
     */
    private $assetSource;

    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payPalConfig = $this->getMockBuilder(PayPalConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->braintreeAdapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->ccConfig = $this->getMockBuilder(CcConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetSource = $this->getMockBuilder(Source::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configProvider = new ConfigProvider(
            $this->config,
            $this->payPalConfig,
            $this->braintreeAdapter,
            $this->ccConfig,
            $this->assetSource
        );
    }

    /**
     * Run test getConfig method
     *
     * @param array $config
     * @param array $expected
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($config, $expected)
    {
        $this->markTestSkipped('Skip this test');
        $this->braintreeAdapter->expects(static::once())
            ->method('generate')
            ->willReturn(self::CLIENT_TOKEN);

        foreach ($config as $method => $value) {
            $this->config->expects(static::once())
                ->method($method)
                ->willReturn($value);
        }

        static::assertEquals($expected, $this->configProvider->getConfig());
    }

    /**
     * @covers \PayPal\Braintree\Model\Ui\ConfigProvider::getClientToken
     * @dataProvider getClientTokenDataProvider
     */
    public function testGetClientToken($merchantAccountId, $params)
    {
        $this->config->expects(static::once())
            ->method('getMerchantAccountId')
            ->willReturn($merchantAccountId);

        $this->braintreeAdapter->expects(static::once())
            ->method('generate')
            ->with($params)
            ->willReturn(self::CLIENT_TOKEN);

        static::assertEquals(self::CLIENT_TOKEN, $this->configProvider->getClientToken());
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            [
                'config' => [
                    'isActive' => true,
                    'getCcTypesMapper' => ['visa' => 'VI', 'american-express'=> 'AE'],
                    'getCountrySpecificCardTypeConfig' => [
                        'GB' => ['VI', 'AE'],
                        'US' => ['DI', 'JCB']
                    ],
                    'getAvailableCardTypes' => ['AE', 'VI', 'MC', 'DI', 'JCB'],
                    'isCvvEnabled' => true,
                    'isVerify3DSecure' => true,
                    'is3DSAlwaysRequested' => true,
                    'getThresholdAmount' => (float)20,
                    'get3DSecureSpecificCountries' => ['GB', 'US', 'CA'],
                    'getEnvironment' => 'test-environment',
                    'getMerchantId' => 'test-merchant-id',
                ],
                'expected' => [
                    'payment' => [
                        ConfigProvider::CODE => [
                            'isActive' => true,
                            'clientToken' => self::CLIENT_TOKEN,
                            'ccTypesMapper' => ['visa' => 'VI', 'american-express' => 'AE'],
                            'sdkUrl' => self::SDK_URL,
                            'countrySpecificCardTypes' =>[
                                'GB' => ['VI', 'AE'],
                                'US' => ['DI', 'JCB']
                            ],
                            'availableCardTypes' => ['AE', 'VI', 'MC', 'DI', 'JCB'],
                            'useCvv' => true,
                            'environment' => 'test-environment',
                            'merchantId' => 'test-merchant-id',
                            'ccVaultCode' => ConfigProvider::CC_VAULT_CODE,
                            'style' => [
                                'shape' => null,
                                'size' => null,
                                'color' => null
                            ],
                            'disabledFunding' => [
                                'card' => null,
                                'elv' => null
                            ],
                            'icons' => []
                        ],
                        Config::CODE_3DSECURE => [
                            'enabled' => true,
                            'challengeRequested' => true,
                            'thresholdAmount' => 20,
                            'specificCountries' => ['GB', 'US', 'CA'],
                            'useCvvVault' => null
                        ],
                        ConfigProvider::CC_VAULT_CODE => [
                            'useCvvVault' => null
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getClientTokenDataProvider()
    {
        return [
            [
                'merchantAccountId' => '',
                'params' => []
            ],
            [
                'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                'params' => ['merchantAccountId' => self::MERCHANT_ACCOUNT_ID]
            ]
        ];
    }
}
