<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Config\PayPal;

use Magento\Store\Model\ScopeInterface;
use PayPal\Braintree\Model\Config\Source\Color;
use PayPal\Braintree\Model\Config\Source\Shape;
use PayPal\Braintree\Model\Config\Source\Size;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\CcConfig;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const KEY_TITLE = 'title';
    const KEY_DISPLAY_ON_SHOPPING_CART = 'display_on_shopping_cart';
    const KEY_ALLOW_TO_EDIT_SHIPPING_ADDRESS = 'allow_shipping_address_override';
    const KEY_MERCHANT_NAME_OVERRIDE = 'merchant_name_override';
    const KEY_REQUIRE_BILLING_ADDRESS = 'require_billing_address';
    const KEY_PAYPAL_DISABLED_FUNDING_CHECKOUT = 'disabled_funding_checkout';
    const KEY_PAYPAL_DISABLED_FUNDING_CART = 'disabled_funding_cart';
    const KEY_PAYPAL_DISABLED_FUNDING_PDP = 'disabled_funding_productpage';
    const BUTTON_AREA_CART = 'cart';
    const BUTTON_AREA_CHECKOUT = 'checkout';
    const BUTTON_AREA_PDP = 'productpage';
    const KEY_BUTTON_COLOR = 'color';
    const KEY_BUTTON_SHAPE = 'shape';
    const KEY_BUTTON_SIZE = 'size';

    /**
     * @var CcConfig
     */
    private $ccConfig;

    /**
     * @var array
     */
    private $icon = [];

    /**
     * @var Size
     */
    private $sizeConfigSource;

    /**
     * @var Color
     */
    private $colorConfigSource;

    /**
     * @var Shape
     */
    private $shapeConfigSource;

    /**
     * @var Shape
     */
    private $scopeConfigResolver;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param CcConfig $ccConfig
     * @param Size $sizeConfigSource
     * @param Color $colorConfigSource
     * @param Shape $shapeConfigSource
     * @param null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CcConfig $ccConfig,
        Size $sizeConfigSource,
        Color $colorConfigSource,
        Shape $shapeConfigSource,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->scopeConfigResolver = $scopeConfig;
        $this->ccConfig = $ccConfig;
        $this->sizeConfigSource = $sizeConfigSource;
        $this->colorConfigSource = $colorConfigSource;
        $this->shapeConfigSource = $shapeConfigSource;
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * @return bool
     */
    public function isDisplayShoppingCart(): bool
    {
        return (bool) $this->getValue(self::KEY_DISPLAY_ON_SHOPPING_CART);
    }

    /**
     * Is shipping address can be editable on PayPal side
     *
     * @return bool
     */
    public function isAllowToEditShippingAddress(): bool
    {
        return (bool) $this->getValue(self::KEY_ALLOW_TO_EDIT_SHIPPING_ADDRESS);
    }

    /**
     * Get merchant name to display in PayPal popup
     *
     * @return string|null
     */
    public function getMerchantName()
    {
        return $this->getValue(self::KEY_MERCHANT_NAME_OVERRIDE);
    }

    /**
     * Get Merchant country
     *
     * @param int $storeId
     * @return mixed|null
     */
    public function getMerchantCountry()
    {
        return $this->scopeConfigResolver->getValue(
            'paypal/general/merchant_country',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is billing address can be required
     *
     * @return string
     */
    public function isRequiredBillingAddress(): string
    {
        return $this->getValue(self::KEY_REQUIRE_BILLING_ADDRESS);
    }

    /**
     * Get title of payment
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->getValue(self::KEY_TITLE);
    }

    /**
     * Retrieve the button style config values
     *
     * @param $area
     * @param $style
     * @return string|array
     */
    private function getButtonStyle($area, $style)
    {
        $useCustom = $this->getValue('button_customise_' . $area);
        if ($useCustom) {
            $value = $this->getValue('button_' . $style . '_' . $area);
        } else {
            $defaults = [
                'button_color_cart' => 2,
                'button_size_cart' => 2,
                'button_shape_cart' => 1,
                'button_color_checkout' => 2,
                'button_size_checkout' => 2,
                'button_shape_checkout' => 1,
                'button_color_productpage' => 2,
                'button_size_productpage' => 2,
                'button_shape_productpage' => 1
            ];
            $value = $defaults['button_' . $style . '_' . $area];
        }

        return $value;
    }

    /**
     * Get button color mapped to the value expected by the PayPal API
     *
     * @param string $area
     * @return string|null
     */
    public function getButtonColor($area = self::BUTTON_AREA_CART)
    {
        $value = $this->getButtonStyle($area, self::KEY_BUTTON_COLOR);
        $options = $this->colorConfigSource->toRawValues();
        return $options[$value];
    }

    /**
     * Get button shape mapped to the value expected by the PayPal API
     *
     * @param string $area
     * @return string
     */
    public function getButtonShape($area = self::BUTTON_AREA_CART)
    {
        $value = $this->getButtonStyle($area, self::KEY_BUTTON_SHAPE);
        $options = $this->shapeConfigSource->toRawValues();
        return $options[$value];
    }

    /**
     * Get button size mapped to the value expected by the PayPal API
     *
     * @param string $area
     * @return string
     */
    public function getButtonSize($area = self::BUTTON_AREA_CART)
    {
        $value = $this->getButtonStyle($area, self::KEY_BUTTON_SIZE);
        $options = $this->sizeConfigSource->toRawValues();
        return $options[$value];
    }

    /**
     * Get PayPal icon
     *
     * @return array
     */
    public function getPayPalIcon(): array
    {
        if (empty($this->icon)) {
            $asset = $this->ccConfig->createAsset('PayPal_Braintree::images/paypal.png');
            list($width, $height) = getimagesize($asset->getSourceFile());
            $this->icon = [
                'url' => $asset->getUrl(),
                'width' => $width,
                'height' => $height
            ];
        }

        return $this->icon;
    }

    /**
     * Disabled paypal funding options - Card
     *
     * @param string|self $area
     * @return bool
     */
    public function getDisabledFundingOptionCard($area = null): bool
    {
        if (!$area) {
            $area = self::KEY_PAYPAL_DISABLED_FUNDING_CHECKOUT;
        }

        if ($value = $this->getValue($area)) {
            if (strpos($value, 'card') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Disabled paypal funding options - ELV
     *
     * @param string|self $area
     * @return bool
     */
    public function getDisabledFundingOptionElv($area = null): bool
    {
        if (!$area) {
            $area = self::KEY_PAYPAL_DISABLED_FUNDING_CHECKOUT;
        }

        if ($value = $this->getValue($area)) {
            if (strpos($value, 'elv') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * PayPal btn enabled product page
     *
     * @return bool
     */
    public function getProductPageBtnEnabled(): bool
    {
        return $this->getValue('button_productpage_enabled');
    }
}
