<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    public const ALIAS_ELEMENT_INDEX = 'alias';
    public const BUTTON_ELEMENT_INDEX = 'button_id';

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
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
     * Get Container Id
     *
     * @return string
     */
    public function getContainerId(): string
    {
        return $this->getData(self::BUTTON_ELEMENT_INDEX);
    }

    /**
     * Get Locale
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * Get currency
     *
     * @return string|null
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrency()
    {
        return $this->checkoutSession->getQuote()->getCurrency()->getBaseCurrencyCode();
    }

    /**
     * Get amount
     *
     * @return float
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAmount()
    {
        return $this->checkoutSession->getQuote()->getBaseGrandTotal();
    }

    /**
     * Is active
     *
     * @return bool
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isActive(): bool
    {
        return $this->payment->isAvailable($this->checkoutSession->getQuote()) &&
            $this->config->isDisplayShoppingCart();
    }

    /**
     * Is PayPal credit active
     *
     * @return bool
     */
    public function isCreditActive(): bool
    {
        return $this->payPalCreditConfig->isActive();
    }

    /**
     * Is PayPal pay later active
     *
     * @return bool
     */
    public function isPayLaterActive(): bool
    {
        return $this->payPalPayLaterConfig->isActive();
    }

    /**
     * Is Pay Later message active
     *
     * @param string $type
     * @return bool
     */
    public function isPayLaterMessageActive($type): bool
    {
        return $this->payPalPayLaterConfig->isMessageActive($type);
    }

    /**
     * Is show PayPal Button
     *
     * @param string $type
     * @param string $location
     * @return bool
     */
    public function showPayPalButton(string $type, string $location): bool
    {
        return $this->config->showPayPalButton($type, $location);
    }
    /**
     * Is Pay Later button active
     *
     * @param string $type
     * @return bool
     */
    public function isPayLaterButtonActive($type): bool
    {
        return $this->payPalPayLaterConfig->isButtonActive($type);
    }

    /**
     * Is PayPal vault active
     *
     * @return bool
     */
    public function isPayPalVaultActive(): bool
    {
        return (bool) $this->payPalPayLaterConfig->isPayPalVaultActive();
    }

    /**
     * Get Merchant Name
     *
     * @return string|null
     */
    public function getMerchantName()
    {
        return $this->config->getMerchantName();
    }

    /**
     * Get Button Shape
     *
     * @param string $type
     * @return string
     */
    public function getButtonShape(string $type): string
    {
        return $this->config->getButtonShape(Config::BUTTON_AREA_CART, $type);
    }

    /**
     * Get Button Color
     *
     * @param string $type
     * @return string
     */
    public function getButtonColor(string $type): string
    {
        return $this->config->getButtonColor(Config::BUTTON_AREA_CART, $type);
    }

    /**
     * Get Button Size
     *
     * @param string $type
     * @return string
     */
    public function getButtonSize(string $type): string
    {
        return $this->config->getButtonSize(Config::BUTTON_AREA_CART, $type);
    }

    /**
     * Get Button Layout
     *
     * @param string $type
     * @inheritDoc
     */
    public function getButtonLayout(string $type): string
    {
        return $this->config->getButtonLayout(Config::BUTTON_AREA_CART, $type);
    }

    /**
     * Get Button Tagline
     *
     * @param string $type
     * @inheritDoc
     */
    public function getButtonTagline(string $type): string
    {
        return $this->config->getButtonTagline(Config::BUTTON_AREA_CART, $type);
    }

    /**
     * Get Button Label
     *
     * @param string $type
     * @inheritDoc
     */
    public function getButtonLabel(string $type): string
    {
        return $this->config->getButtonLabel(Config::BUTTON_AREA_CART, $type);
    }

    /**
     * Get Environment
     *
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getEnvironment(): string
    {
        return $this->braintreeConfig->getEnvironment();
    }

    /**
     * Get Client Token
     *
     * @return string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getClientToken()
    {
        return $this->configProvider->getClientToken();
    }

    /**
     * Get Action Success
     *
     * @return string
     */
    public function getActionSuccess(): string
    {
        return $this->getUrl(ConfigProvider::CODE . '/paypal/review', ['_secure' => true]);
    }

    /**
     * Get Disabled Funding
     *
     * @return array
     */
    public function getDisabledFunding(): array
    {
        return [
            'card' => $this->config->isFundingOptionCardDisabled(Config::KEY_PAYPAL_DISABLED_FUNDING_CART),
            'elv' => $this->config->isFundingOptionElvDisabled(Config::KEY_PAYPAL_DISABLED_FUNDING_CART)
        ];
    }

    /**
     * Get Extra Class name
     *
     * @return string
     */
    public function getExtraClassname(): string
    {
        return $this->getIsCart() ? 'cart' : 'minicart';
    }

    /**
     * Is Required Billing Address
     *
     * @return bool
     */
    public function isRequiredBillingAddress(): bool
    {
        return (bool) $this->config->isRequiredBillingAddress();
    }

    /**
     * Get Merchant Country
     *
     * @return string|null
     */
    public function getMerchantCountry()
    {
        return $this->payPalPayLaterConfig->getMerchantCountry();
    }

    /**
     * Get Messaging Layout
     *
     * @param string $type
     * @inheritDoc
     */
    public function getMessagingLayout(string $type): string
    {
        return $this->config->getMessagingStyle(Config::BUTTON_AREA_CART, $type, 'layout');
    }

    /**
     * Get Messaging Logo
     *
     * @param string $type
     * @inheritDoc
     */
    public function getMessagingLogo(string $type): string
    {
        return $this->config->getMessagingStyle(Config::BUTTON_AREA_CART, $type, 'logo');
    }

    /**
     * Get Messaging Logo Position
     *
     * @param string $type
     * @inheritDoc
     */
    public function getMessagingLogoPosition(string $type): string
    {
        return $this->config->getMessagingStyle(Config::BUTTON_AREA_CART, $type, 'logo_position');
    }

    /**
     * Get Messaging Text Color
     *
     * @param string $type
     * @inheritDoc
     */
    public function getMessagingTextColor(string $type): string
    {
        return $this->config->getMessagingStyle(Config::BUTTON_AREA_CART, $type, 'text_color');
    }
}
