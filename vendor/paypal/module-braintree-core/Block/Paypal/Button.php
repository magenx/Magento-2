<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PayPal\Braintree\Block\Paypal;

use PayPal\Braintree\Gateway\Config\PayPal\Config;
use PayPal\Braintree\Model\Ui\ConfigProvider;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\MethodInterface;
use PayPal\Braintree\Gateway\Config\Config as BraintreeConfig;
use PayPal\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;
use PayPal\Braintree\Gateway\Config\PayPalPayLater\Config as PayPalPayLaterConfig;

class Button extends Template implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';
    const BUTTON_ELEMENT_INDEX = 'button_id';

    /**
     * @var ResolverInterface $localeResolver
     */
    private $localeResolver;

    /**
     * @var Session $checkoutSession
     */
    private $checkoutSession;

    /**
     * @var Config $config
     */
    protected $config;

    /**
     * @var BraintreeConfig $braintreeConfig
     */
    private $braintreeConfig;

    /**
     * @var ConfigProvider $configProvider
     */
    private $configProvider;

    /**
     * @var MethodInterface $payment
     */
    private $payment;

    /**
     * @var PayPalCreditConfig $payPalCreditConfig
     */
    private $payPalCreditConfig;

    /**
     * @var PayPalPayLaterConfig $payPalPayLaterConfig
     */
    private $payPalPayLaterConfig;

    /**
     * Button constructor
     *
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param Session $checkoutSession
     * @param Config $config
     * @param PayPalCreditConfig $payPalCreditConfig
     * @param PayPalPayLaterConfig $payPalPayLaterConfig
     * @param BraintreeConfig $braintreeConfig
     * @param ConfigProvider $configProvider
     * @param MethodInterface $payment
     * @param array $data
     */
    public function __construct(
        Context $context,
        ResolverInterface $localeResolver,
        Session $checkoutSession,
        Config $config,
        PayPalCreditConfig $payPalCreditConfig,
        PayPalPayLaterConfig $payPalPayLaterConfig,
        BraintreeConfig $braintreeConfig,
        ConfigProvider $configProvider,
        MethodInterface $payment,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->localeResolver = $localeResolver;
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->braintreeConfig = $braintreeConfig;
        $this->configProvider = $configProvider;
        $this->payment = $payment;
        $this->payPalCreditConfig = $payPalCreditConfig;
        $this->payPalPayLaterConfig = $payPalPayLaterConfig;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml(): string
    {
        if ($this->isActive()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function getAlias(): string
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getContainerId(): string
    {
        return $this->getData(self::BUTTON_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * @return string|null
     */
    public function getCurrency()
    {
        return $this->checkoutSession->getQuote()->getCurrency()->getBaseCurrencyCode();
    }

    /**
     * @return float|null
     */
    public function getAmount()
    {
        return $this->checkoutSession->getQuote()->getBaseGrandTotal();
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->payment->isAvailable($this->checkoutSession->getQuote()) &&
            $this->config->isDisplayShoppingCart();
    }

    /**
     * @return bool
     */
    public function isCreditActive(): bool
    {
        return $this->payPalCreditConfig->isActive();
    }

    /**
     * @return bool
     */
    public function isPayLaterActive(): bool
    {
        return $this->payPalPayLaterConfig->isActive();
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isPayLaterMessageActive($type): bool
    {
        return $this->payPalPayLaterConfig->isMessageActive($type);
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isPayLaterButtonActive($type): bool
    {
        return $this->payPalPayLaterConfig->isButtonActive($type);
    }

    /**
     * @return bool
     */
    public function isPayPalVaultActive(): bool
    {
        return $this->payPalPayLaterConfig->IsPayPalVaultActive();
    }


    /**
     * @return string|null
     */
    public function getMerchantName()
    {
        return $this->config->getMerchantName();
    }

    /**
     * @return string
     */
    public function getButtonShape(): string
    {
        return $this->config->getButtonShape(Config::BUTTON_AREA_CART);
    }

    /**
     * @return string
     */
    public function getButtonColor(): string
    {
        return $this->config->getButtonColor(Config::BUTTON_AREA_CART);
    }

    /**
     * @return string
     */
    public function getButtonSize(): string
    {
        return $this->config->getButtonSize(Config::BUTTON_AREA_CART);
    }

    /**
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getEnvironment(): string
    {
        return $this->braintreeConfig->getEnvironment();
    }

    /**
     * @return string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getClientToken()
    {
        return $this->configProvider->getClientToken();
    }

    /**
     * @return string
     */
    public function getActionSuccess(): string
    {
        return $this->getUrl(ConfigProvider::CODE . '/paypal/review', ['_secure' => true]);
    }

    /**
     * @return array
     */
    public function getDisabledFunding(): array
    {
        return [
            'card' => $this->config->getDisabledFundingOptionCard(Config::KEY_PAYPAL_DISABLED_FUNDING_CART),
            'elv' => $this->config->getDisabledFundingOptionElv(Config::KEY_PAYPAL_DISABLED_FUNDING_CART)
        ];
    }

    /**
     * @return string
     */
    public function getExtraClassname(): string
    {
        return $this->getIsCart() ? 'cart' : 'minicart';
    }

    /**
     * @return bool
     */
    public function isRequiredBillingAddress(): bool
    {
        return (bool) $this->config->isRequiredBillingAddress();
    }

    /**
     * @return string|null
     */
    public function getMerchantCountry()
    {
        return $this->payPalPayLaterConfig->getMerchantCountry();
    }
}
