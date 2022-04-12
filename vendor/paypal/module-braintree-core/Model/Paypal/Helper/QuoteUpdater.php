<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model\Paypal\Helper;

use InvalidArgumentException;
use Magento\Directory\Model\Region;
use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;
use PayPal\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use PayPal\Braintree\Observer\DataAssignObserver;
use PayPal\Braintree\Gateway\Config\PayPal\Config;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Model\Quote\Address;

class QuoteUpdater extends AbstractHelper
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var Region
     */
    private $region;

    /**
     * QuoteUpdater constructor
     *
     * @param Config $config
     * @param CartRepositoryInterface $quoteRepository
     * @param ManagerInterface $eventManager
     * @param ResourceConnection $resource
     * @param Region $region
     */
    public function __construct(
        Config $config,
        CartRepositoryInterface $quoteRepository,
        ManagerInterface $eventManager,
        ResourceConnection $resource,
        Region $region
    ) {
        $this->config = $config;
        $this->quoteRepository = $quoteRepository;
        $this->eventManager = $eventManager;
        $this->resource = $resource;
        $this->region = $region;
    }

    /**
     * Execute operation
     *
     * @param string $nonce
     * @param array $details
     * @param Quote $quote
     * @return void
     * @throws InvalidArgumentException
     * @throws LocalizedException
     */
    public function execute($nonce, array $details, Quote $quote)
    {
        if (empty($nonce) || empty($details)) {
            throw new InvalidArgumentException('The "nonce" and "details" fields do not exist.');
        }

        $payment = $quote->getPayment();
        $payment->setMethod(ConfigProvider::PAYPAL_CODE);
        $payment->setAdditionalInformation(DataAssignObserver::PAYMENT_METHOD_NONCE, $nonce);
        $this->updateQuote($quote, $details);
    }

    /**
     * Update quote data
     *
     * @param Quote $quote
     * @param array $details
     * @return void
     */
    private function updateQuote(Quote $quote, array $details)
    {
        $this->eventManager->dispatch('braintree_paypal_update_quote_before', [
            'quote' => $quote,
            'paypal_response' => $details
        ]);

        $quote->setMayEditShippingAddress(false);
        $quote->setMayEditShippingMethod(true);

        $this->updateQuoteAddress($quote, $details);
        $this->disabledQuoteAddressValidation($quote);

        $quote->collectTotals();

        /**
         * Unset shipping assignment to prevent from saving / applying outdated data
         * @see \Magento\Quote\Model\QuoteRepository\SaveHandler::processShippingAssignment
         */
        if ($quote->getExtensionAttributes()) {
            $quote->getExtensionAttributes()->setShippingAssignments(null);
        }

        $this->quoteRepository->save($quote);
        $this->cleanUpAddress($quote);

        $this->eventManager->dispatch('braintree_paypal_update_quote_after', [
            'quote' => $quote,
            'paypal_response' => $details
        ]);
    }

    /**
     * @param Quote $quote
     */
    private function cleanUpAddress(Quote $quote)
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('quote_address');
        $connection->delete(
            $tableName,
            'quote_id = ' . (int) $quote->getId() . ' AND email IS NULL'
        );
    }

    /**
     * Update quote address
     *
     * @param Quote $quote
     * @param array $details
     * @return void
     */
    private function updateQuoteAddress(Quote $quote, array $details)
    {
        if (!$quote->getIsVirtual()) {
            $this->updateShippingAddress($quote, $details);
        }

        $this->updateBillingAddress($quote, $details);
    }

    /**
     * Update shipping address
     * (PayPal doesn't provide detailed shipping info: prefix, suffix)
     *
     * @param Quote $quote
     * @param array $details
     * @return void
     */
    private function updateShippingAddress(Quote $quote, array $details)
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setFirstname($details['shippingAddress']['recipientFirstName']);
        $shippingAddress->setLastname($details['shippingAddress']['recipientLastName']);
        $shippingAddress->setEmail($details['email']);

        $shippingAddress->setCollectShippingRates(true);

        $this->updateAddressData($shippingAddress, $details['shippingAddress']);

        // PayPal's address supposes not saving against customer account
        $shippingAddress->setSaveInAddressBook(false);
        $shippingAddress->setSameAsBilling(false);
        $shippingAddress->unsCustomerAddressId();
        $shippingAddress->setCustomerAddressId(null);
    }

    /**
     * Update billing address
     *
     * @param Quote $quote
     * @param array $details
     * @return void
     */
    private function updateBillingAddress(Quote $quote, array $details)
    {
        $billingAddress = $quote->getBillingAddress();
        $billingAddress->setFirstname($details['shippingAddress']['recipientFirstName']);
        $billingAddress->setLastname($details['shippingAddress']['recipientLastName']);
        $billingAddress->setEmail($details['email']);

        if ($this->config->isRequiredBillingAddress() && !empty($details['billingAddress'])) {
            $billingAddress->setFirstname($details['firstName']);
            $billingAddress->setLastname($details['lastName']);

            $this->updateAddressData($billingAddress, $details['billingAddress']);
        } else {
            $this->updateAddressData($billingAddress, $details['shippingAddress']);
        }

        // PayPal's address supposes not saving against customer account
        $billingAddress->setSaveInAddressBook(false);
        $billingAddress->setSameAsBilling(false);
        $billingAddress->setCustomerAddressId(null);
    }

    /**
     * Sets address data from exported address
     *
     * @param Address $address
     * @param array $addressData
     * @return void
     */
    private function updateAddressData(Address $address, array $addressData)
    {
        $street = $addressData['streetAddress'];

        if (isset($addressData['extendedAddress'])) {
            $street = $street . ' ' . $addressData['extendedAddress'];
        }
        $address->setStreet($street);
        $address->setCity($addressData['locality']);

        $address->setRegion($addressData['region']);

        // Setting the region is not enough, we have to set the region ID.
        $regionId = $this->region->loadByCode($addressData['region'], $addressData['countryCodeAlpha2'])->getId();
        $address->setRegionId($regionId);

        $address->setCountryId($addressData['countryCodeAlpha2']);
        $address->setPostcode($addressData['postalCode']);

        if (!empty($addressData['telephone'])) {
            $address->setTelephone($addressData['telephone']);
        }

        // PayPal's address supposes not saving against customer account
        $address->setSaveInAddressBook(false);
        $address->setSameAsBilling(false);
    }
}
