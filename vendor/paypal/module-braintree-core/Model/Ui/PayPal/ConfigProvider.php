<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model\Ui\PayPal;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;
use PayPal\Braintree\Gateway\Config\PayPal\Config;
use PayPal\Braintree\Gateway\Config\PayPalCredit\Config as CreditConfig;
use PayPal\Braintree\Gateway\Config\PayPalPayLater\Config as PayLaterConfig;

class ConfigProvider implements ConfigProviderInterface
{
    const PAYPAL_CODE = 'braintree_paypal';
    const PAYPAL_CREDIT_CODE = 'braintree_paypal_credit';
    const PAYPAL_PAYLATER_CODE = 'braintree_paypal_paylater';
    const PAYPAL_VAULT_CODE = 'braintree_paypal_vault';

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
     * ConfigProvider constructor.
     * @param Config $config
     * @param CreditConfig $creditConfig
     * @param PayLaterConfig $payLaterConfig
     * @param ResolverInterface $resolver
     */
    public function __construct(
        Config $config,
        CreditConfig $creditConfig,
        PayLaterConfig $payLaterConfig,
        ResolverInterface $resolver
    ) {
        $this->config = $config;
        $this->creditConfig = $creditConfig;
        $this->payLaterConfig = $payLaterConfig;
        $this->resolver = $resolver;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        $locale = $this->resolver->getLocale();
        if (in_array($locale, ['nb_NO', 'nn_NO'])) {
            $locale = 'no_NO';
        }

        return [
            'payment' => [
                self::PAYPAL_CODE => [
                    'isActive' => $this->config->isActive(),
                    'title' => $this->config->getTitle(),
                    'isAllowShippingAddressOverride' => $this->config->isAllowToEditShippingAddress(),
                    'merchantName' => $this->config->getMerchantName(),
                    'merchantCountry' => $this->config->getMerchantCountry(),
                    'locale' => $locale,
                    'paymentAcceptanceMarkSrc' => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-medium.png',
                    'vaultCode' => self::PAYPAL_VAULT_CODE,
                    'paymentIcon' => $this->config->getPayPalIcon(),
                    'style' => [
                        'shape' => $this->config->getButtonShape(Config::BUTTON_AREA_CHECKOUT),
                        'size' => $this->config->getButtonSize(Config::BUTTON_AREA_CHECKOUT),
                        'color' => $this->config->getButtonColor(Config::BUTTON_AREA_CHECKOUT)
                    ],
                    'isRequiredBillingAddress' => $this->config->isRequiredBillingAddress()
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
                        'shape' => $this->config->getButtonShape(Config::BUTTON_AREA_CHECKOUT),
                        'size' => $this->config->getButtonSize(Config::BUTTON_AREA_CHECKOUT),
                        'color' => $this->config->getButtonColor(Config::BUTTON_AREA_CHECKOUT)
                    ],
                    'isRequiredBillingAddress' => $this->config->isRequiredBillingAddress()
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
                        'shape' => $this->config->getButtonShape(Config::BUTTON_AREA_CHECKOUT),
                        'size' => $this->config->getButtonSize(Config::BUTTON_AREA_CHECKOUT),
                        'color' => $this->config->getButtonColor(Config::BUTTON_AREA_CHECKOUT)
                    ],
                    'isRequiredBillingAddress' => $this->config->isRequiredBillingAddress()
                ]

            ]
        ];
    }
}
