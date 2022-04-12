<?php
namespace PayPal\Braintree\Model\GooglePay\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use PayPal\Braintree\Gateway\Request\PaymentDataBuilder;
use PayPal\Braintree\Model\GooglePay\Config;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\Repository;

class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'braintree_googlepay';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var BraintreeAdapter
     */
    private $adapter;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var \PayPal\Braintree\Gateway\Config\Config
     */
    private $braintreeConfig;

    /**
     * @var string
     */
    private $clientToken = '';

    /**
     * ConfigProvider constructor.
     * @param Config $config
     * @param BraintreeAdapter $adapter
     * @param Repository $assetRepo
     * @param \PayPal\Braintree\Gateway\Config\Config $braintreeConfig
     */
    public function __construct(
        Config $config,
        BraintreeAdapter $adapter,
        Repository $assetRepo,
        \PayPal\Braintree\Gateway\Config\Config $braintreeConfig
    ) {
        $this->config = $config;
        $this->adapter = $adapter;
        $this->assetRepo = $assetRepo;
        $this->braintreeConfig = $braintreeConfig;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        if (!$this->config->isActive()) {
            return [];
        }

        return [
            'payment' => [
                'braintree_googlepay' => [
                    'environment' => $this->getEnvironment(),
                    'clientToken' => $this->getClientToken(),
                    'merchantId' => $this->getMerchantId(),
                    'cardTypes' => $this->getAvailableCardTypes(),
                    'btnColor' => $this->getBtnColor(),
                    'paymentMarkSrc' => $this->getPaymentMarkSrc()
                ]
            ]
        ];
    }

    /**
     * Generate a new client token if necessary
     *
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
     * Get environment
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getEnvironment(): string
    {
        return $this->config->getEnvironment();
    }

    /**
     * Get merchant name
     *
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->config->getMerchantId();
    }

    /**
     * @return array
     */
    public function getBtnColor(): int
    {
        return $this->config->getBtnColor();
    }

    /**
     * @return array
     */
    public function getAvailableCardTypes(): array
    {
        return $this->config->getAvailableCardTypes();
    }

    /**
     * Get the url to the payment mark image
     *
     * @return mixed
     */
    public function getPaymentMarkSrc()
    {
        $fileId = 'PayPal_Braintree::images/GooglePay_AcceptanceMark_WhiteShape_WithStroke_RGB_62x38pt@4x.png';
        return $this->assetRepo->getUrl($fileId);
    }
}
