<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PayPal\Braintree\Block;

use Magento\Backend\Model\Session\Quote;
use PayPal\Braintree\Gateway\Config\Config as GatewayConfig;
use PayPal\Braintree\Model\Adminhtml\Source\CcType;
use PayPal\Braintree\Model\Ui\ConfigProvider;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Config;
use Magento\Payment\Model\MethodInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends Cc
{
    /**
     * @var Quote
     */
    protected $sessionQuote;

    /**
     * @var Config
     */
    protected $gatewayConfig;

    /**
     * @var CcType
     */
    protected $ccType;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $paymentDataHelper;

    /**
     * @param Context $context
     * @param Config $paymentConfig
     * @param Quote $sessionQuote
     * @param GatewayConfig $gatewayConfig
     * @param CcType $ccType
     * @param LoggerInterface $logger
     * @param Data $paymentDataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        Quote $sessionQuote,
        GatewayConfig $gatewayConfig,
        CcType $ccType,
        LoggerInterface $logger,
        Data $paymentDataHelper,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->sessionQuote = $sessionQuote;
        $this->gatewayConfig = $gatewayConfig;
        $this->ccType = $ccType;
        $this->logger = $logger;
        $this->paymentDataHelper = $paymentDataHelper;
    }

    /**
     * @inheritDoc
     */
    public function getCcAvailableTypes(): array
    {
        try {
            $configuredCardTypes = $this->getConfiguredCardTypes();
            $countryId = $this->sessionQuote->getQuote()->getBillingAddress()->getCountryId();
            return $this->filterCardTypesForCountry($configuredCardTypes, $countryId);
        } catch (InputException $e) {
            $this->logger->critical($e->getMessage());
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage());
        }

        return [];
    }

    /**
     * Check if cvv validation is available
     *
     * @return boolean
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function useCvv(): bool
    {
        return $this->gatewayConfig->isCvvEnabled();
    }

    /**
     * Check if vault enabled
     *
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function isVaultEnabled(): bool
    {
        if ($this->sessionQuote->getStoreId()) {
            $storeId = $this->sessionQuote->getStoreId();
        } else {
            $storeId = $this->_storeManager->getStore()->getId();
        }

        $vaultPayment = $this->getVaultPayment();

        return $vaultPayment->isActive($storeId);
    }

    /**
     * Get card types available for Braintree
     *
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function getConfiguredCardTypes(): array
    {
        $types = $this->ccType->getCcTypeLabelMap();
        $configCardTypes = array_fill_keys($this->gatewayConfig->getAvailableCardTypes(), '');

        return array_intersect_key($types, $configCardTypes);
    }

    /**
     * Filter card types for specific country
     *
     * @param array $configCardTypes
     * @param string $countryId
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function filterCardTypesForCountry(array $configCardTypes, string $countryId): array
    {
        $filtered = $configCardTypes;
        $countryCardTypes = $this->gatewayConfig->getCountryAvailableCardTypes($countryId);

        // filter card types only if specific card types are set for country
        if (!empty($countryCardTypes)) {
            $availableTypes = array_fill_keys($countryCardTypes, '');
            $filtered = array_intersect_key($filtered, $availableTypes);
        }

        return $filtered;
    }

    /**
     * Get configured vault payment for Braintree
     *
     * @throws LocalizedException
     */
    private function getVaultPayment(): MethodInterface
    {
        return $this->paymentDataHelper->getMethodInstance(ConfigProvider::CC_VAULT_CODE);
    }
}
