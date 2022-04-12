<?php

namespace PayPal\Braintree\Block\Paypal;

use PayPal\Braintree\Gateway\Config\Config as BraintreeConfig;
use PayPal\Braintree\Gateway\Config\PayPal\Config;
use PayPal\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;
use PayPal\Braintree\Gateway\Config\PayPalPayLater\Config as PayPalPayLaterConfig;
use PayPal\Braintree\Model\Ui\ConfigProvider;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Payment\Model\MethodInterface;
use Magento\Directory\Model\Currency;

class ProductPage extends Button
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Currency
     */
    protected $currency;

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
            return $this->config->getProductPageBtnEnabled();
        }

        return false;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCurrency(): string
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCurrencySymbol(): string
    {
        $r = 1;
        return $this->currency->load($this->getCurrency())->getCurrencySymbol();
    }

    /**
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

        return 100; // TODO There must be a better return value than this?
    }

    /**
     * @return string
     */
    public function getContainerId(): string
    {
        return 'oneclick';
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return 'productpage';
    }

    /**
     * @return string
     */
    public function getActionSuccess(): string
    {
        return $this->getUrl('braintree/paypal/oneclick', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getButtonShape(): string
    {
        return $this->config->getButtonShape(Config::BUTTON_AREA_PDP);
    }

    /**
     * @inheritDoc
     */
    public function getButtonColor(): string
    {
        return $this->config->getButtonColor(Config::BUTTON_AREA_PDP);
    }

    /**
     * @inheritDoc
     */
    public function getButtonSize(): string
    {
        return $this->config->getButtonSize(Config::BUTTON_AREA_PDP);
    }

    /**
     * @inheritDoc
     */
    public function getDisabledFunding(): array
    {
        return [
            'card' => $this->config->getDisabledFundingOptionCard(Config::KEY_PAYPAL_DISABLED_FUNDING_PDP),
            'elv' => $this->config->getDisabledFundingOptionElv(Config::KEY_PAYPAL_DISABLED_FUNDING_PDP)
        ];
    }
}
