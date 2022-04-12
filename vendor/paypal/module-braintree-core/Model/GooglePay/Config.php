<?php

namespace PayPal\Braintree\Model\GooglePay;

use PayPal\Braintree\Model\Adminhtml\Source\GooglePayBtnColor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const KEY_CC_TYPES = 'cctypes';
    const KEY_BTN_COLOR = 'btn_color';

    /**
     * @var \PayPal\Braintree\Gateway\Config\Config
     */
    protected $braintreeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param \PayPal\Braintree\Gateway\Config\Config $braintreeConfig
     * @param null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \PayPal\Braintree\Gateway\Config\Config $braintreeConfig,
        $methodCode = null,
        $pathPattern = \Magento\Payment\Gateway\Config\Config::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->braintreeConfig = $braintreeConfig;
    }

    /**
     * Get merchant name to display
     *
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->getValue('merchant_id');
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
     * Get BTN Color
     *
     * @return int
     */
    public function getBtnColor(): int
    {
        $color = $this->getValue(self::KEY_BTN_COLOR);
        if ($color == GooglePayBtnColor::OPTION_WHITE || $color == GooglePayBtnColor::OPTION_BLACK) {
            return (int) $color;
        }

        return GooglePayBtnColor::OPTION_WHITE;
    }

    /**
     * Get allowed payment card types
     *
     * @return array
     */
    public function getAvailableCardTypes(): array
    {
        $ccTypes = $this->getValue(self::KEY_CC_TYPES);

        return !empty($ccTypes) ? explode(',', $ccTypes) : [];
    }

    /**
     * Map Braintree Environment setting
     *
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getEnvironment(): string
    {
        if ($this->braintreeConfig->getEnvironment() !== 'production') {
            return 'TEST';
        }

        return 'PRODUCTION';
    }
}
