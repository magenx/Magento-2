<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model\Adapter;

use Braintree\ClientToken;
use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\Customer;
use Braintree\Exception\NotFound;
use Braintree\PaymentMethod;
use Braintree\PaymentMethodNonce;
use Braintree\ResourceCollection;
use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Transaction;
use Exception;
use PayPal\Braintree\Gateway\Config\Config;
use PayPal\Braintree\Model\Adminhtml\Source\Environment;
use PayPal\Braintree\Model\StoreConfigResolver;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BraintreeAdapter
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreConfigResolver
     */
    private $storeConfigResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * BraintreeAdapter constructor.
     *
     * @param Config $config Braintree configurator
     * @param StoreConfigResolver $storeConfigResolver StoreId resolver model
     *
     * @param LoggerInterface $logger
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function __construct(
        Config $config,
        StoreConfigResolver $storeConfigResolver,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->storeConfigResolver = $storeConfigResolver;
        $this->logger = $logger;

        $this->initCredentials();
    }

    /**
     * Initializes credentials.
     *
     * @return void
     *
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function initCredentials()
    {
        $storeId = $this->storeConfigResolver->getStoreId();
        $environmentIdentifier = $this->config->getValue(Config::KEY_ENVIRONMENT, $storeId);

        $this->environment(Environment::ENVIRONMENT_SANDBOX);

        $merchantId = $this->config->getValue(Config::KEY_SANDBOX_MERCHANT_ID, $storeId);
        $publicKey = $this->config->getValue(Config::KEY_SANDBOX_PUBLIC_KEY, $storeId);
        $privateKey = $this->config->getValue(Config::KEY_SANDBOX_PRIVATE_KEY, $storeId);

        if ($environmentIdentifier === Environment::ENVIRONMENT_PRODUCTION) {
            $this->environment(Environment::ENVIRONMENT_PRODUCTION);

            $merchantId = $this->config->getValue(Config::KEY_MERCHANT_ID, $storeId);
            $publicKey = $this->config->getValue(Config::KEY_PUBLIC_KEY, $storeId);
            $privateKey = $this->config->getValue(Config::KEY_PRIVATE_KEY, $storeId);
        }

        $this->merchantId(
            $merchantId
        );
        $this->publicKey(
            $publicKey
        );
        $this->privateKey(
            $privateKey
        );
    }

    /**
     * Init environment
     *
     * @param string|null $value
     * @return mixed
     */
    public function environment(string $value = null)
    {
        return Configuration::environment($value);
    }

    /**
     * Init merchant id
     *
     * @param string|null $value
     * @return mixed
     */
    public function merchantId(string $value = null)
    {
        return Configuration::merchantId($value);
    }

    /**
     * Init public key
     *
     * @param string|null $value
     * @return mixed
     */
    public function publicKey(string $value = null)
    {
        return Configuration::publicKey($value);
    }

    /**
     * Init private key
     *
     * @param string|null $value
     * @return mixed
     */
    public function privateKey($value = null)
    {
        return Configuration::privateKey($value);
    }

    /**
     * Generate client token
     *
     * @param array $params
     * @return string
     */
    public function generate(array $params = [])
    {
        try {
            return ClientToken::generate($params);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return '';
        }
    }

    /**
     * Find token
     *
     * @param string $token
     * @return CreditCard|null
     */
    public function find(string $token)
    {
        try {
            return CreditCard::find($token);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return null;
        }
    }

    /**
     * Search transactions
     *
     * @param array $filters
     * @return ResourceCollection|null
     */
    public function search(array $filters)
    {
        return Transaction::search($filters);
    }

    /**
     * Find transaction by Id
     *
     * @param string $id
     * @return NotFound|Successful
     */
    public function findById(string $id)
    {
        return Transaction::find($id);
    }

    /**
     * Create payment nonce
     *
     * @param string $token
     * @return Error|PaymentMethodNonce
     */
    public function createNonce(string $token)
    {
        return PaymentMethodNonce::create($token);
    }

    /**
     * Transaction sale
     *
     * @param array $attributes
     * @return Successful|Error
     */
    public function sale(array $attributes)
    {
        return Transaction::sale($attributes);
    }

    /**
     * Submit transaction for settlement
     *
     * @param string $transactionId
     * @param null|float $amount
     * @param array $attribs
     * @return Successful|Error
     */
    public function submitForSettlement(string $transactionId, $amount = null, $attribs = [])
    {
        return Transaction::submitForSettlement($transactionId, $amount, $attribs);
    }

    /**
     * Submit transaction for partial settlement
     *
     * @param string $transactionId
     * @param null|float $amount
     * @param array $attribs
     * @return Successful|Error
     */
    public function submitForPartialSettlement(string $transactionId, $amount = null, $attribs = [])
    {
        return Transaction::submitForPartialSettlement($transactionId, $amount, $attribs);
    }

    /**
     * Void transaction
     *
     * @param string $transactionId
     * @return Successful|Error
     */
    public function void(string $transactionId)
    {
        return Transaction::void($transactionId);
    }

    /**
     * Refund transaction
     *
     * @param string $transactionId
     * @param null|float $amount
     * @return Successful|Error
     */
    public function refund(string $transactionId, $amount = null)
    {
        return Transaction::refund($transactionId, $amount);
    }

    /**
     * Clone original transaction
     *
     * @param string $transactionId
     * @param array $attributes
     * @return mixed
     */
    public function cloneTransaction(string $transactionId, array $attributes)
    {
        return Transaction::cloneTransaction($transactionId, $attributes);
    }

    /**
     * Delete payment method token
     *
     * @param string $token
     * @return mixed
     */
    public function deletePaymentMethod(string $token)
    {
        return PaymentMethod::delete($token)->success;
    }

    /**
     * Update payment method token
     *
     * @param string $token
     * @param array $attribs
     * @return mixed
     */
    public function updatePaymentMethod(string $token, array $attribs)
    {
        return PaymentMethod::update($token, $attribs);
    }

    /**
     * Get customer by Id
     *
     * @param string $id
     * @return Customer
     * @throws NotFound
     */
    public function getCustomerById(string $id)
    {
        return Customer::find($id);
    }
}
