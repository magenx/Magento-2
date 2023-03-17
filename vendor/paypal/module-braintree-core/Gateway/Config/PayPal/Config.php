<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Braintree\Gateway\Config\PayPal;

use Magento\Store\Model\ScopeInterface;
use PayPal\Braintree\Model\Config\Source\Color;
use PayPal\Braintree\Model\Config\Source\Shape;
use PayPal\Braintree\Model\Config\Source\Size;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\CcConfig;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    public const KEY_ACTIVE = 'active';
    public const KEY_TITLE = 'title';
    public const KEY_DISPLAY_ON_SHOPPING_CART = 'display_on_shopping_cart';
    public const KEY_ALLOW_TO_EDIT_SHIPPING_ADDRESS = 'allow_shipping_address_override';
    public const KEY_MERCHANT_NAME_OVERRIDE = 'merchant_name_override';
    public const KEY_REQUIRE_BILLING_ADDRESS = 'require_billing_address';
    public const KEY_PAYPAL_DISABLED_FUNDING_CHECKOUT = 'disabled_funding_checkout';
    public const KEY_PAYPAL_DISABLED_FUNDING_CART = 'disabled_funding_cart';
    public const KEY_PAYPAL_DISABLED_FUNDING_PDP = 'disabled_funding_productpage';
    public const BUTTON_AREA_CART = 'cart';
    public const BUTTON_AREA_CHECKOUT = 'checkout';
    public const BUTTON_AREA_PDP = 'productpage';
    public const KEY_BUTTON_COLOR = 'color';
    public const KEY_BUTTON_SHAPE = 'shape';
    public const KEY_BUTTON_SIZE = 'size';
    public const KEY_BUTTON_LABEL = 'label';

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
     * @param string|null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CcConfig $ccConfig,
        Size $sizeConfigSource,
        Color $colorConfigSource,
        Shape $shapeConfigSource,
        string $methodCode = null,
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
     * Is button display on shopping cart
     *
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
     * @param string $area
     * @param string $style
     * @param string $type
     * @return string|array
     */
    private function getButtonStyle(string $area, string $style, string $type)
    {
        return $this->getValue('button_location_' . $area . '_type_' . $type . '_' . $style);
    }

    /**
     * Get button color mapped to the value expected by the PayPal API
     *
     * @param string $area
     * @param string $type
     * @return string|null
     */
    public function getButtonColor(string $area = self::BUTTON_AREA_CART, string $type = 'paypal')
    {
        $value = $this->getButtonStyle($area, self::KEY_BUTTON_COLOR, $type);
        $options = $this->colorConfigSource->toRawValues();
        return $options[$value];
    }

    /**
     * Get button shape mapped to the value expected by the PayPal API
     *
     * @param string $area
     * @param string $type
     * @return string
     */
    public function getButtonShape(string $area = self::BUTTON_AREA_CART, string $type = 'paypal')
    {
        $value = $this->getButtonStyle($area, self::KEY_BUTTON_SHAPE, $type);
        $options = $this->shapeConfigSource->toRawValues();
        return $options[$value];
    }

    /**
     * Get button size mapped to the value expected by the PayPal API
     *
     * @param string $area
     * @param string $type
     * @return string
     */
    public function getButtonSize(string $area = self::BUTTON_AREA_CART, string $type = 'paypal')
    {
        $value = $this->getButtonStyle($area, self::KEY_BUTTON_SIZE, $type);
        $options = $this->sizeConfigSource->toRawValues();
        return $options[$value];
    }

    /**
     * Get button label mapped to the value expected by the PayPal API
     *
     * @param string $area
     * @param string $type
     * @return string
     */
    public function getButtonLabel(string $area = self::BUTTON_AREA_CART, string $type = 'paypal')
    {
        return $this->getButtonStyle($area, self::KEY_BUTTON_LABEL, $type);
    }

    /**
     * Get button layout mapped to the value expected by the PayPal API
     *
     * @param string $area
     * @param string $type
     * @param string $style
     * @return string
     */
    public function getMessagingStyle(
        string $area = self::BUTTON_AREA_CART,
        string $type = 'paypal',
        string $style = 'layout'
    ) {
        return $this->getButtonStyle($area, $style, $type);
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
            list($width, $height) = getimagesizefromstring($asset->getSourceFile());
            $this->icon = [
                'url' => $asset->getUrl(),
                'width' => $width,
                'height' => $height
            ];
        }

        return $this->icon;
    }

    /**
     * Disabled PayPal funding options - Card
     *
     * @param string|null $area
     * @return bool
     */
    public function isFundingOptionCardDisabled(string $area = null): bool
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
     * Disabled PayPal funding options - ELV
     *
     * @param string|null $area
     * @return bool
     */
    public function isFundingOptionElvDisabled(string $area = null): bool
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
    public function isProductPageButtonEnabled(): bool
    {
        return (bool) $this->getValue('button_location_productpage_type_paypal_show');
    }

    /**
     * Show PayPal button status
     *
     * @param string $type
     * @param string $location
     * @return bool
     */
    public function showPayPalButton(string $type, string $location): bool
    {
        $field = 'button_location_' . $location . '_type_' . $type . '_show';
        return (bool) $this->getValue($field);
    }
}
