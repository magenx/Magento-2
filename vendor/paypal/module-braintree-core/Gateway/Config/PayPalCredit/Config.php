<?php
declare(strict_types=1);

namespace PayPal\Braintree\Gateway\Config\PayPalCredit;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Store\Model\ScopeInterface;
use \Magento\Paypal\Model\Config as PPConfig;

class Config implements ConfigInterface
{
    const KEY_ACTIVE = 'active';
    const KEY_UK_ACTIVATION_CODE = 'uk_activation_code';
    const KEY_UK_MERCHANT_NAME = 'uk_merchant_name';
    const KEY_CLIENT_ID = 'client_id';
    const KEY_SECRET = 'secret';
    const KEY_SANDBOX = 'sandbox';
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
        $paypalCreditActive = $this->getConfigValue("payment/braintree_paypal_credit/active");
        $paypalCreditShow =
            $this->getConfigValue("payment/braintree_paypal/button_location_checkout_type_credit_show");

        // If PayPal or PayPal Credit is disabled in the admin
        if (!$paypalActive || !$paypalCreditActive || !$paypalCreditShow) {
            return false;
        }

        // Only allowed on US and UK
        if (!$this->isUk() && !$this->isUS()) {
            return false;
        }

        // Validate configuration if UK
        if ($this->isUk()) {
            $merchantId = substr($this->getConfigValue('payment/braintree/merchant_id'), -4);
            return $merchantId === $this->getActivationCode() && $this->getMerchantName();
        }

        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * Calculator is only used on UK view
     *
     * @return bool
     */
    public function isCalculatorEnabled(): bool
    {
        return ($this->isUk() && $this->isActive());
    }

    /**
     * UK Merchant Name
     *
     * @return string|null
     */
    public function getMerchantName()
    {
        return $this->getValue(self::KEY_UK_MERCHANT_NAME);
    }

    /**
     * UK Activation Code
     *
     * @return string|null
     */
    public function getActivationCode()
    {
        return $this->getValue(self::KEY_UK_ACTIVATION_CODE);
    }

    /**
     * PayPal Sandbox mode
     *
     * @return bool
     */
    public function isSandbox(): bool
    {
        return 'sandbox' === $this->getConfigValue('payment/braintree/environment');
    }

    /**
     * Client ID
     *
     * @return string|null
     */
    public function getClientId()
    {
        return $this->getValue(self::KEY_CLIENT_ID);
    }

    /**
     * Secret Key
     * @return string|null
     */
    public function getSecret()
    {
        return $this->getValue(self::KEY_SECRET);
    }

    /**
     * Merchant Country set to GB/UK
     * @return bool
     */
    public function isUk(): bool
    {
        return 'GB' === $this->getMerchantCountry();
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
}
