<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model\Ui\PayPal;

use Braintree\Result\Error;
use Braintree\Result\Successful;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use PayPal\Braintree\Gateway\Config\Config as BraintreeConfig;
use PayPal\Braintree\Gateway\Config\PayPal\Config;
use PayPal\Braintree\Gateway\Config\PayPalCredit\Config as CreditConfig;
use PayPal\Braintree\Gateway\Config\PayPalPayLater\Config as PayLaterConfig;
use PayPal\Braintree\Gateway\Request\PaymentDataBuilder;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;

class ConfigProvider implements ConfigProviderInterface
{
    public const PAYPAL_CODE = 'braintree_paypal';
    public const PAYPAL_CREDIT_CODE = 'braintree_paypal_credit';
    public const PAYPAL_PAYLATER_CODE = 'braintree_paypal_paylater';
    public const PAYPAL_VAULT_CODE = 'braintree_paypal_vault';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var CreditConfig
     */
    private $creditConfig;

    /**
     * @var PayLaterConfig
     */
    private $payLaterConfig;

    /**
     * @var string
     */
    private $clientToken = '';

    /**
     * @var BraintreeConfig
     */
    private $braintreeConfig;

    /**
     * @var BraintreeAdapter
     */
    private $braintreeAdapter;

    /**
     * ConfigProvider constructor.
     *
     * @param Config $config
     * @param CreditConfig $creditConfig
     * @param PayLaterConfig $payLaterConfig
     * @param ResolverInterface $resolver
     * @param BraintreeConfig $braintreeConfig
     * @param BraintreeAdapter $braintreeAdapter
     */
    public function __construct(
        Config $config,
        CreditConfig $creditConfig,
        PayLaterConfig $payLaterConfig,
        ResolverInterface $resolver,
        BraintreeConfig $braintreeConfig,
        BraintreeAdapter $braintreeAdapter,
    ) {
        $this->config = $config;
        $this->creditConfig = $creditConfig;
        $this->payLaterConfig = $payLaterConfig;
        $this->resolver = $resolver;
        $this->braintreeConfig = $braintreeConfig;
        $this->braintreeAdapter = $braintreeAdapter;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return \array[][]
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getConfig(): array
    {
        if (!$this->config->isActive()) {
            return [];
        }

        $locale = $this->resolver->getLocale();
        if (in_array($locale, ['nb_NO', 'nn_NO'])) {
            $locale = 'no_NO';
        }

        return [
            'payment' => [
                self::PAYPAL_CODE => [
                    'isActive' => $this->config->isActive(),
                    'clientToken' => $this->getClientToken(),
                    'title' => $this->config->getTitle(),
                    'isAllowShippingAddressOverride' => $this->config->isAllowToEditShippingAddress(),
                    'merchantName' => $this->config->getMerchantName(),
                    'environment' => $this->braintreeConfig->getEnvironment(),
                    'merchantCountry' => $this->config->getMerchantCountry(),
                    'locale' => $locale,
                    'paymentAcceptanceMarkSrc' => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-medium.png',
                    'vaultCode' => self::PAYPAL_VAULT_CODE,
                    'paymentIcon' => $this->config->getPayPalIcon(),
                    'style' => [
                        'shape' => $this->config->getButtonShape(Config::BUTTON_AREA_CHECKOUT, 'paypal'),
                        'size' => $this->config->getButtonSize(Config::BUTTON_AREA_CHECKOUT, 'paypal'),
                        'color' => $this->config->getButtonColor(Config::BUTTON_AREA_CHECKOUT, 'paypal'),
                        'label' => $this->config->getButtonLabel(Config::BUTTON_AREA_CHECKOUT, 'paypal')
                    ],
                    'isRequiredBillingAddress' => $this->config->isRequiredBillingAddress(),
                    'canSendLineItems' => $this->braintreeConfig->canSendLineItems()
                ],

                self::PAYPAL_CREDIT_CODE => [
                    'isActive' => $this->creditConfig->isActive(),
                    'title' => __('PayPal Credit'),
                    'isAllowShippingAddressOverride' => $this->config->isAllowToEditShippingAddress(),
                    'merchantName' => $this->config->getMerchantName(),
                    'merchantCountry' => $this->config->getMerchantCountry(),
                    'locale' => $locale,
                    'paymentAcceptanceMarkSrc' => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppc-acceptance-medium.png',
                    'paymentIcon' => $this->config->getPayPalIcon(),
                    'style' => [
                        'shape' => $this->config->getButtonShape(Config::BUTTON_AREA_CHECKOUT, 'credit'),
                        'size' => $this->config->getButtonSize(Config::BUTTON_AREA_CHECKOUT, 'credit'),
                        'color' => $this->config->getButtonColor(Config::BUTTON_AREA_CHECKOUT, 'credit'),
                        'label' => $this->config->getButtonLabel(Config::BUTTON_AREA_CHECKOUT, 'credit')
                    ],
                    'isRequiredBillingAddress' => $this->config->isRequiredBillingAddress(),
                    'canSendLineItems' => $this->braintreeConfig->canSendLineItems()
                ],

                self::PAYPAL_PAYLATER_CODE => [
                    'isActive' => $this->payLaterConfig->isButtonActive('checkout'),
                    'title' => __('PayPal PayLater'),
                    'isAllowShippingAddressOverride' => $this->config->isAllowToEditShippingAddress(),
                    'merchantName' => $this->config->getMerchantName(),
                    'merchantCountry' => $this->config->getMerchantCountry(),
                    'locale' => $locale,
                    'paymentAcceptanceMarkSrc' => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppc-acceptance-medium.png',
                    'paymentIcon' => $this->config->getPayPalIcon(),
                    'isMessageActive' => $this->payLaterConfig->isMessageActive('checkout'),
                    'style' => [
                        'shape' => $this->config->getButtonShape(Config::BUTTON_AREA_CHECKOUT, 'paylater'),
                        'size' => $this->config->getButtonSize(Config::BUTTON_AREA_CHECKOUT, 'paylater'),
                        'color' => $this->config->getButtonColor(Config::BUTTON_AREA_CHECKOUT, 'paylater'),
                        'label' => $this->config->getButtonLabel(Config::BUTTON_AREA_CHECKOUT, 'paylater')
                    ],
                    'message' => [
                        'layout' => $this->config->getMessagingStyle(
                            Config::BUTTON_AREA_CHECKOUT,
                            'messaging',
                            'layout'
                        ),
                        'logo' => $this->config->getMessagingStyle(
                            Config::BUTTON_AREA_CHECKOUT,
                            'messaging',
                            'logo'
                        ),
                        'logo_position' => $this->config->getMessagingStyle(
                            Config::BUTTON_AREA_CHECKOUT,
                            'messaging',
                            'logo_position'
                        ),
                        'text_color' => $this->config->getMessagingStyle(
                            Config::BUTTON_AREA_CHECKOUT,
                            'messaging',
                            'text_color'
                        )
                    ],
                    'isRequiredBillingAddress' => $this->config->isRequiredBillingAddress(),
                    'canSendLineItems' => $this->braintreeConfig->canSendLineItems()
                ]
            ]
        ];
    }

    /**
     * Generate a new client token if necessary
     *
     * @return Error|Successful|string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getClientToken(): Error|Successful|string|null
    {
        if (empty($this->clientToken)) {
            $params = [];

            $merchantAccountId = $this->braintreeConfig->getMerchantAccountId();
            if (!empty($merchantAccountId)) {
                $params[PaymentDataBuilder::MERCHANT_ACCOUNT_ID] = $merchantAccountId;
            }

            $this->clientToken = $this->braintreeAdapter->generate($params);
        }

        return $this->clientToken;
    }
}
