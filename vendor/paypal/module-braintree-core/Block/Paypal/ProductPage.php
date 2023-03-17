<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PayPal\Braintree\Block\Paypal;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Directory\Model\Currency;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Payment\Model\MethodInterface;
use PayPal\Braintree\Gateway\Config\Config as BraintreeConfig;
use PayPal\Braintree\Gateway\Config\PayPal\Config;
use PayPal\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;
use PayPal\Braintree\Gateway\Config\PayPalPayLater\Config as PayPalPayLaterConfig;
use PayPal\Braintree\Model\Ui\ConfigProvider;

/**
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class ProductPage extends Button
{
    /**
     * @var Registry
     */
    protected Registry $registry;

    /**
     * @var Currency
     */
    protected Currency $currency;

    /**
     * ProductPage constructor.
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param Session $checkoutSession
     * @param Config $config
     * @param PayPalCreditConfig $payPalCreditConfig
     * @param PayPalPayLaterConfig $payPalPayLaterConfig
     * @param BraintreeConfig $braintreeConfig
     * @param ConfigProvider $configProvider
     * @param MethodInterface $payment
     * @param Registry $registry
     * @param Currency $currency
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
        Registry $registry,
        Currency $currency,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $localeResolver,
            $checkoutSession,
            $config,
            $payPalCreditConfig,
            $payPalPayLaterConfig,
            $braintreeConfig,
            $configProvider,
            $payment,
            $data
        );

        $this->registry = $registry;
        $this->currency = $currency;
    }

    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        if (parent::isActive() === true) {
            return $this->config->isProductPageButtonEnabled();
        }

        return false;
    }

    /**
     * Get Currency
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getCurrency(): string
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * Get currency symbol
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getCurrencySymbol(): string
    {
        return $this->currency->load($this->getCurrency())->getCurrencySymbol();
    }

    /**
     * Get final amount of product
     *
     * @return float
     */
    public function getAmount(): float
    {
        /** @var Product $product */
        $product = $this->registry->registry('product');
        if ($product) {
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                return $product->getFinalPrice();
            }
            if ($product->getTypeId() === Grouped::TYPE_CODE) {
                $groupedProducts = $product->getTypeInstance()->getAssociatedProducts($product);
                return $groupedProducts[0]->getPrice();
            }

            return $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        }

        return 100.00; // TODO There must be a better return value than this?
    }

    /**
     * Get container Id
     *
     * @return string
     */
    public function getContainerId(): string
    {
        return 'oneclick';
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation(): string
    {
        return 'productpage';
    }

    /**
     * Get action success url
     *
     * @return string
     */
    public function getActionSuccess(): string
    {
        return $this->getUrl('braintree/paypal/oneclick', ['_secure' => true]);
    }

    /**
     * Get button shape
     *
     * @param string $type
     * @return string
     */
    public function getButtonShape(string $type): string
    {
        return $this->config->getButtonShape(Config::BUTTON_AREA_PDP, $type);
    }

    /**
     * Get button color
     *
     * @param string $type
     * @return string
     */
    public function getButtonColor(string $type): string
    {
        return $this->config->getButtonColor(Config::BUTTON_AREA_PDP, $type);
    }

    /**
     * Get button size
     *
     * @param string $type
     * @return string
     */
    public function getButtonSize(string $type): string
    {
        return $this->config->getButtonSize(Config::BUTTON_AREA_PDP, $type);
    }

    /**
     * Get button label
     *
     * @param string $type
     * @return string
     */
    public function getButtonLabel(string $type): string
    {
        return $this->config->getButtonLabel(Config::BUTTON_AREA_PDP, $type);
    }

    /**
     * @inheritDoc
     */
    public function getDisabledFunding(): array
    {
        return [
            'card' => $this->config->isFundingOptionCardDisabled(Config::KEY_PAYPAL_DISABLED_FUNDING_PDP),
            'elv' => $this->config->isFundingOptionElvDisabled(Config::KEY_PAYPAL_DISABLED_FUNDING_PDP)
        ];
    }

    /**
     * Get messaging layout
     *
     * @param string $type
     * @return string
     */
    public function getMessagingLayout(string $type): string
    {
        return $this->config->getMessagingStyle(Config::BUTTON_AREA_PDP, $type, 'layout');
    }

    /**
     * Get messaging logo
     *
     * @param string $type
     * @return string
     */
    public function getMessagingLogo(string $type): string
    {
        return $this->config->getMessagingStyle(Config::BUTTON_AREA_PDP, $type, 'logo');
    }

    /**
     * Get messaging logo position
     *
     * @param string $type
     * @return string
     */
    public function getMessagingLogoPosition(string $type): string
    {
        return $this->config->getMessagingStyle(Config::BUTTON_AREA_PDP, $type, 'logo_position');
    }

    /**
     * Get messaging text color
     *
     * @param string $type
     * @return string
     */
    public function getMessagingTextColor(string $type): string
    {
        return $this->config->getMessagingStyle(Config::BUTTON_AREA_PDP, $type, 'text_color');
    }

    /**
     *
     *
     * @return array
     */
    public function getCartLineItems(): array
    {
        // @TODO manage line items request from PDP for the PayPal buttons
        return [];
    }
}
