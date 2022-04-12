<?php

namespace PayPal\Braintree\Gateway\Config\PayPalPayLater;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Store\Model\ScopeInterface;
use \Magento\Paypal\Model\Config as PPConfig;

class Config implements ConfigInterface
{
    const KEY_ACTIVE = 'active';
    const DEFAULT_PATH_PATTERN = 'payment/%s/%s';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string|null
     */
    private $methodCode;

    /**
     * @var string|null
     */
    private $pathPattern;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string|null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->methodCode = $methodCode;
        $this->pathPattern = $pathPattern;
    }

    /**
     * Sets method code
     *
     * @param string $methodCode
     * @return void
     */
    public function setMethodCode($methodCode)
    {
        $this->methodCode = $methodCode;
    }

    /**
     * Sets path pattern
     *
     * @param string $pathPattern
     * @return void
     */
    public function setPathPattern($pathPattern)
    {
        $this->pathPattern = $pathPattern;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getValue($field, $storeId = null)
    {
        if (null === $this->methodCode || null === $this->pathPattern) {
            return null;
        }

        return $this->scopeConfig->getValue(
            sprintf($this->pathPattern, $this->methodCode, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $field
     * @return mixed
     */
    public function getConfigValue($field)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $paypalActive = $this->getConfigValue("payment/braintree_paypal/active");
        $paypalPayLaterActive = $this->getConfigValue("payment/braintree_paypal_paylater/active");

        // If PayPal or PayPal Pay Later is disabled in the admin
        if (!$paypalActive || !$paypalPayLaterActive) {
            return false;
        }

        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * Get Paypal pay later message configuration status
     * @param string $type
     *
     * @return bool
     */
    public function isMessageActive($type): bool
    {
        $paypalActive = $this->getConfigValue("payment/braintree_paypal/active");
        $paypalPayLaterActive = $this->getConfigValue("payment/braintree_paypal_paylater/active");
        $paypalPayLaterMessageActive = $this->getConfigValue("payment/braintree_paypal/message_" . $type . "_enable");

        // If PayPal or PayPal Pay Later is disabled in the admin
        if (!$paypalActive || !$paypalPayLaterMessageActive || $this->IsPayPalVaultActive()) {
            return false;
        }

        if (!in_array($this->getMerchantCountry(), ['GB','FR','US','DE', 'AU'])) {
            return false;
        }

        return (bool) $paypalPayLaterMessageActive;
    }

    /**
     * Get Paypal pay later button configuration status
     * @param string $type
     *
     * @return bool
     */
    public function isButtonActive($type): bool
    {
        $paypalActive = $this->getConfigValue("payment/braintree_paypal/active");
        $paypalPayLaterActive = $this->getConfigValue("payment/braintree_paypal_paylater/active");
        $paypalPayLaterButtonActive = $this->getConfigValue("payment/braintree_paypal/button_paylater_" . $type . "_enable");

        // If PayPal or PayPal Pay Later is disabled in the admin
        if (!$paypalActive || !$paypalPayLaterActive || !$paypalPayLaterButtonActive) {
            return false;
        }

        return (bool) $paypalPayLaterButtonActive;
    }

    /**
     * Merchant Country set to US
     * @return bool
     */
    public function isUS(): bool
    {
        return 'US' === $this->getMerchantCountry();
    }

    /**
     * Merchant Country
     *
     * @return string|null
     */
    public function getMerchantCountry()
    {
        return $this->getConfigValue('paypal/general/merchant_country');
    }

    /**
     * Get PayPal Vault status
     *
     * @return bool
     */
    public function IsPayPalVaultActive(): bool
    {
        return $this->getConfigValue('payment/braintree_paypal_vault/active');
    }
}
