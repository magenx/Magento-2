<?php

namespace PayPal\Braintree\Model\GooglePay;

use PayPal\Braintree\Api\Data\AuthDataInterfaceFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;

class Auth
{
    /**
     * @var AuthDataInterfaceFactory
     */
    private $authData;

    /**
     * @var Ui\ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Auth constructor
     *
     * @param AuthDataInterfaceFactory $authData
     * @param Ui\ConfigProvider $configProvider
     * @param UrlInterface $url
     * @param CustomerSession $customerSession
     */
    public function __construct(
        AuthDataInterfaceFactory $authData,
        Ui\ConfigProvider $configProvider,
        UrlInterface $url,
        CustomerSession $customerSession
    ) {
        $this->authData = $authData;
        $this->configProvider = $configProvider;
        $this->url = $url;
        $this->customerSession = $customerSession;
    }

    /**
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getClientToken(): string
    {
        return $this->configProvider->getClientToken();
    }

    /**
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getEnvironment(): string
    {
        return $this->configProvider->getEnvironment();
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->configProvider->getMerchantId();
    }

    /**
     * @return string
     */
    public function getActionSuccess(): string
    {
        return $this->url->getUrl('checkout/onepage/success', ['_secure' => true]);
    }

    /**
     * @return array
     */
    public function getAvailableCardTypes(): array
    {
        return $this->configProvider->getAvailableCardTypes();
    }

    /**
     * @return int
     */
    public function getBtnColor(): int
    {
        return $this->configProvider->getBtnColor();
    }
}
