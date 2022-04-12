<?php
declare(strict_types=1);

namespace PayPal\Braintree\Model\Ach\Ui;

use PayPal\Braintree\Gateway\Config\Config as BraintreeConfig;
use PayPal\Braintree\Gateway\Request\PaymentDataBuilder;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'braintree_ach_direct_debit';

    const CONFIG_MERCHANT_COUNTRY = 'paypal/general/merchant_country';

    const CONFIG_STORE_NAME = 'general/store_information/name';

    const CONFIG_STORE_URL = 'web/unsecure/base_url';

    const ALLOWED_MERCHANT_COUNTRIES = ['US'];

    const METHOD_KEY_ACTIVE = 'payment/braintree_ach_direct_debit/active';

    /**
     * @var BraintreeAdapter $adapter
     */
    private $adapter;
    /**
     * @var BraintreeConfig $braintreeConfig
     */
    private $braintreeConfig;
    /**
     * @var ScopeConfigInterface $scopeConfig
     */
    private $scopeConfig;
    /**
     * @var string $clientToken
     */
    private $clientToken = '';

    /**
     * ConfigProvider constructor.
     *
     * @param BraintreeAdapter $adapter
     * @param BraintreeConfig $braintreeConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        BraintreeAdapter $adapter,
        BraintreeConfig $braintreeConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->adapter = $adapter;
        $this->braintreeConfig = $braintreeConfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getConfig(): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        return [
            'payment' => [
                self::METHOD_CODE => [
                    'isActive' => $this->isActive(),
                    'clientToken' => $this->getClientToken(),
                    'storeName' => $this->getStoreName()
                ]
            ]
        ];
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::METHOD_KEY_ACTIVE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * ACH is for the US only.
     * Logic based on Merchant Country Location config option.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $merchantCountry = $this->scopeConfig->getValue(
            self::CONFIG_MERCHANT_COUNTRY,
            ScopeInterface::SCOPE_STORE
        );

        return in_array($merchantCountry, self::ALLOWED_MERCHANT_COUNTRIES, true);
    }

    /**
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getClientToken(): string
    {
        if (empty($this->clientToken)) {
            $params = [];

            $merchantAccountId = $this->braintreeConfig->getMerchantAccountId();
            if (!empty($merchantAccountId)) {
                $params[PaymentDataBuilder::MERCHANT_ACCOUNT_ID] = $merchantAccountId;
            }

            $this->clientToken = $this->adapter->generate($params);
        }

        return $this->clientToken;
    }

    /**
     * @return string
     */
    public function getStoreName(): string
    {
        $storeName = $this->scopeConfig->getValue(
            self::CONFIG_STORE_NAME,
            ScopeInterface::SCOPE_STORE
        );

        // If store name is empty, use the base URL
        if (!$storeName) {
            $storeName = $this->scopeConfig->getValue(
                self::CONFIG_STORE_URL,
                ScopeInterface::SCOPE_STORE
            );
        }
        return $storeName;
    }
}
