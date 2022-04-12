<?php
declare(strict_types=1);

namespace PayPal\Braintree\Model\Lpm;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\Repository;
use PayPal\Braintree\Gateway\Config\Config as BraintreeConfig;
use PayPal\Braintree\Gateway\Request\PaymentDataBuilder;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use PayPal\Braintree\Model\StoreConfigResolver;

/**
 * Provide configuration for LPMs
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const KEY_ALLOWED_METHODS = 'allowed_methods';
    const KEY_TITLE = 'title';

    const VALUE_BANCONTACT = 'bancontact';
    const VALUE_EPS = 'eps';
    const VALUE_GIROPAY = 'giropay';
    const VALUE_IDEAL = 'ideal';
    const VALUE_SOFORT = 'sofort';
    const VALUE_MYBANK = 'mybank';
    const VALUE_P24 = 'p24';
    const VALUE_SEPA = 'sepa';

    const LABEL_BANCONTACT = 'Bancontact';
    const LABEL_EPS = 'EPS';
    const LABEL_GIROPAY = 'giropay';
    const LABEL_IDEAL = 'iDEAL';
    const LABEL_SOFORT = 'Klarna Pay Now / SOFORT';
    const LABEL_MYBANK = 'MyBank';
    const LABEL_P24 = 'P24';
    const LABEL_SEPA = 'SEPA/ELV Direct Debit';

    const COUNTRIES_BANCONTACT = 'BE';
    const COUNTRIES_EPS = 'AT';
    const COUNTRIES_GIROPAY = 'DE';
    const COUNTRIES_IDEAL = 'NL';
    const COUNTRIES_SOFORT = ['AT', 'BE', 'DE', 'ES', 'IT', 'NL', 'GB'];
    const COUNTRIES_MYBANK = 'IT';
    const COUNTRIES_P24 = 'PL';
    const COUNTRIES_SEPA = ['AT', 'DE'];

    /**
     * @var StoreConfigResolver
     */
    private $storeConfigResolver;

    /**
     * @var string
     */
    private $clientToken = '';

    /**
     * @var BraintreeAdapter
     */
    private $adapter;

    /**
     * @var BraintreeConfig
     */
    private $braintreeConfig;

    /**
     * @var array
     */
    private $allowedMethods;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @param StoreConfigResolver $storeConfigResolver
     * {@inheritDoc}
     */
    public function __construct(
        BraintreeAdapter $adapter,
        BraintreeConfig $braintreeConfig,
        StoreConfigResolver $storeConfigResolver,
        Repository $assetRepo,
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = \Magento\Payment\Gateway\Config\Config::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->adapter = $adapter;
        $this->braintreeConfig = $braintreeConfig;
        $this->storeConfigResolver = $storeConfigResolver;
        $this->assetRepo = $assetRepo;
    }

    /**
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function isActive(): bool
    {
        return (bool) $this->getValue(
            self::KEY_ACTIVE,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getAllowedMethods(): array
    {
        $allowedMethodsValue = $this->getValue(
            self::KEY_ALLOWED_METHODS,
            $this->storeConfigResolver->getStoreId()
        );
        if (is_null($allowedMethodsValue)) {
            return [];
        }
        $allowedMethods = explode(
            ',',
            $allowedMethodsValue
        );

        foreach ($allowedMethods as $allowedMethod) {
            $this->allowedMethods[] = [
                'method' => $allowedMethod,
                'label' => constant('self::LABEL_' . strtoupper($allowedMethod)),
                'countries' => constant('self::COUNTRIES_' . strtoupper($allowedMethod))
            ];
        }

        return $this->allowedMethods;
    }

    /**
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getClientToken(): string
    {
        if (empty($this->clientToken) && $this->isActive()) {
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
     * @return mixed|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getMerchantAccountId()
    {
        return $this->braintreeConfig->getMerchantAccountId();
    }

    /**
     * @return array
     */
    public function getPaymentIcons(): array
    {
        $icons = [
            self::VALUE_BANCONTACT => $this->assetRepo->getUrl('PayPal_Braintree::images/' . self::VALUE_BANCONTACT . '.svg'),
            self::VALUE_EPS => $this->assetRepo->getUrl('PayPal_Braintree::images/' . self::VALUE_EPS . '.svg'),
            self::VALUE_GIROPAY => $this->assetRepo->getUrl('PayPal_Braintree::images/' . self::VALUE_GIROPAY . '.svg'),
            self::VALUE_IDEAL => $this->assetRepo->getUrl('PayPal_Braintree::images/' . self::VALUE_IDEAL . '.svg'),
            self::VALUE_SOFORT => $this->assetRepo->getUrl('PayPal_Braintree::images/' . self::VALUE_SOFORT . '.svg'),
            self::VALUE_MYBANK => $this->assetRepo->getUrl('PayPal_Braintree::images/' . self::VALUE_MYBANK . '.svg'),
            self::VALUE_P24 => $this->assetRepo->getUrl('PayPal_Braintree::images/' . self::VALUE_P24 . '.svg'),
            self::VALUE_SEPA => $this->assetRepo->getUrl('PayPal_Braintree::images/' . self::VALUE_SEPA . '.svg')
        ];

        return $icons;
    }

    /**
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getTitle(): string
    {
        return $this->getValue(
            self::KEY_TITLE,
            $this->storeConfigResolver->getStoreId()
        );
    }
}
