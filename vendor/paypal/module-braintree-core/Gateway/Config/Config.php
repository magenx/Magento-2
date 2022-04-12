<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use PayPal\Braintree\Model\Adminhtml\Source\Environment;
use PayPal\Braintree\Model\StoreConfigResolver;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ENVIRONMENT = 'environment';
    const KEY_ACTIVE = 'active';
    const KEY_MERCHANT_ID = 'merchant_id';
    const KEY_MERCHANT_ACCOUNT_ID = 'merchant_account_id';
    const KEY_PUBLIC_KEY = 'public_key';
    const KEY_PRIVATE_KEY = 'private_key';
    const KEY_SANDBOX_MERCHANT_ID = 'sandbox_merchant_id';
    const KEY_SANDBOX_PUBLIC_KEY = 'sandbox_public_key';
    const KEY_SANDBOX_PRIVATE_KEY = 'sandbox_private_key';
    const KEY_COUNTRY_CREDIT_CARD = 'countrycreditcard';
    const KEY_CC_TYPES = 'cctypes';
    const KEY_CC_TYPES_BRAINTREE_MAPPER = 'cctypes_braintree_mapper';
    const KEY_USE_CVV = 'useccv';
    const KEY_USE_CVV_VAULT = 'useccv_vault';
    const KEY_VERIFY_3DSECURE = 'verify_3dsecure';
    const KEY_THRESHOLD_AMOUNT = 'threshold_amount';
    const KEY_VERIFY_ALLOW_SPECIFIC = 'verify_all_countries';
    const KEY_VERIFY_SPECIFIC = 'verify_specific_countries';
    const VALUE_3DSECURE_ALL = 0;
    const CODE_3DSECURE = 'three_d_secure';
    const KEY_KOUNT_MERCHANT_ID = 'kount_id';
    const KEY_KOUNT_SKIP_ADMIN = 'kount_skip_admin';
    const FRAUD_PROTECTION = 'fraudprotection';
    const FRAUD_PROTECTION_THRESHOLD = 'fraudprotection_threshold';

    /**
     * Get list of available dynamic descriptors keys
     * @var array
     */
    private static $dynamicDescriptorKeys = [
        'name', 'phone', 'url'
    ];

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var StoreConfigResolver
     */
    private $storeConfigResolver;

    /**
     * Config constructor.
     * @param StoreConfigResolver $storeConfigResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param null $methodCode
     * @param string $pathPattern
     * @param Json|null $serializer
     */
    public function __construct(
        StoreConfigResolver $storeConfigResolver,
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN,
        Json $serializer = null
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->storeConfigResolver = $storeConfigResolver;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * Return the country specific card type config
     *
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getCountrySpecificCardTypeConfig(): array
    {
        $countryCardTypes = $this->getValue(
            self::KEY_COUNTRY_CREDIT_CARD,
            $this->storeConfigResolver->getStoreId()
        );
        if (!$countryCardTypes) {
            return [];
        }
        $countryCardTypes = $this->serializer->unserialize($countryCardTypes);
        return is_array($countryCardTypes) ? $countryCardTypes : [];
    }

    /**
     * Retrieve available credit card types
     *
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getAvailableCardTypes(): array
    {
        $ccTypes = $this->getValue(
            self::KEY_CC_TYPES,
            $this->storeConfigResolver->getStoreId()
        );

        return !empty($ccTypes) ? explode(',', $ccTypes) : [];
    }

    /**
     * Retrieve mapper between Magento and Braintree card types
     *
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getCcTypesMapper(): array
    {
        $result = json_decode(
            $this->getValue(
                self::KEY_CC_TYPES_BRAINTREE_MAPPER,
                $this->storeConfigResolver->getStoreId()
            ),
            true
        );

        return is_array($result) ? $result : [];
    }

    /**
     * Get list of card types available for country
     * @param string $country
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getCountryAvailableCardTypes($country): array
    {
        $types = $this->getCountrySpecificCardTypeConfig();

        return !empty($types[$country]) ? $types[$country] : [];
    }

    /**
     * Check if cvv field is enabled
     *
     * @return boolean
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function isCvvEnabled(): bool
    {
        return (bool) $this->getValue(
            self::KEY_USE_CVV,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Check if cvv field is enabled for vaulted cards
     *
     * @return boolean
     */
    public function isCvvEnabledVault(): bool
    {
        return false;
    }

    /**
     * Check if 3d secure verification enabled
     *
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function isVerify3DSecure(): bool
    {
        return (bool) $this->getValue(
            self::KEY_VERIFY_3DSECURE,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Get threshold amount for 3d secure
     *
     * @return float
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getThresholdAmount(): float
    {
        return (double) $this->getValue(
            self::KEY_THRESHOLD_AMOUNT,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Get list of specific countries for 3d secure
     *
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function get3DSecureSpecificCountries(): array
    {
        $keyVerifyAllowSpecific = $this->getValue(
            self::KEY_VERIFY_ALLOW_SPECIFIC,
            $this->storeConfigResolver->getStoreId()
        );
        if ((int) $keyVerifyAllowSpecific === self::VALUE_3DSECURE_ALL) {
            return [];
        }

        return explode(
            ',',
            $this->getValue(
                self::KEY_VERIFY_SPECIFIC,
                $this->storeConfigResolver->getStoreId()
            )
        );
    }

    /**
     * Get environment
     *
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getEnvironment(): string
    {
        return $this->getValue(
            self::KEY_ENVIRONMENT,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Get Kount Merchant Id
     *
     * @return string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getKountMerchantId()
    {
        return $this->getValue(
            self::KEY_KOUNT_MERCHANT_ID,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function canSkipAdminFraudProtection(): bool
    {
        return (bool) $this->getValue(self::KEY_KOUNT_SKIP_ADMIN, $this->storeConfigResolver->getStoreId());
    }

    /**
     * Get Merchant Id
     *
     * @return string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getMerchantId()
    {
        if ($this->getEnvironment() === Environment::ENVIRONMENT_SANDBOX) {
            return $this->getValue(
                self::KEY_SANDBOX_MERCHANT_ID,
                $this->storeConfigResolver->getStoreId()
            );
        }
        return $this->getValue(
            self::KEY_MERCHANT_ID,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Check for fraud protection
     *
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function hasFraudProtection(): bool
    {
        return (bool) $this->getValue(
            self::FRAUD_PROTECTION,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Get fraud protection threshold
     *
     * @return float|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getFraudProtectionThreshold()
    {
        return $this->getValue(
            self::FRAUD_PROTECTION_THRESHOLD,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Get Payment configuration status
     *
     * @param int|null $storeId
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function isActive(int $storeId = null): bool
    {
        return (bool) $this->getValue(
            self::KEY_ACTIVE,
            $storeId ?? $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Get list of configured dynamic descriptors
     *
     * @return array
     */
    public function getDynamicDescriptors(): array
    {
        $values = [];
        foreach (self::$dynamicDescriptorKeys as $key) {
            $value = $this->getValue('descriptor_' . $key);
            if (!empty($value)) {
                $values[$key] = $value;
            }
        }
        return $values;
    }

    /**
     * Get Merchant account ID
     *
     * @param int $storeId
     * @return mixed|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getMerchantAccountId(int $storeId = null)
    {
        return $this->getValue(
            self::KEY_MERCHANT_ACCOUNT_ID,
            $storeId ?? $this->storeConfigResolver->getStoreId()
        );
    }
}
