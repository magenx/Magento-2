<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @param string|null $value
     * @return mixed
     */
    public function environment($value = null)
    {
        return Configuration::environment($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function merchantId($value = null)
    {
        return Configuration::merchantId($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function publicKey($value = null)
    {
        return Configuration::publicKey($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function privateKey($value = null)
    {
        return Configuration::privateKey($value);
    }

    /**
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
     * @param string $token
     * @return CreditCard|null
     */
    public function find($token)
    {
        try {
            return CreditCard::find($token);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return null;
        }
    }

    /**
     * @param array $filters
     * @return ResourceCollection|null
     */
    public function search(array $filters)
    {
        return Transaction::search($filters);
    }

    /**
     * @param string $id
     * @return Transaction|null
     */
    public function findById(string $id)
    {
        return Transaction::find($id);
    }

    /**
     * @param string $token
     * @return Successful|Error
     */
    public function createNonce($token)
    {
        return PaymentMethodNonce::create($token);
    }

    /**
     * @param array $attributes
     * @return Successful|Error
     */
    public function sale(array $attributes)
    {
        return Transaction::sale($attributes);
    }

    /**
     * @param string $transactionId
     * @param null|float $amount
     * @return Successful|Error
     */
    public function submitForSettlement($transactionId, $amount = null)
    {
        return Transaction::submitForSettlement($transactionId, $amount);
    }

    /**
     * @param string $transactionId
     * @param null|float $amount
     * @return Successful|Error
     */
    public function submitForPartialSettlement($transactionId, $amount = null)
    {
        return Transaction::submitForPartialSettlement($transactionId, $amount);
    }

    /**
     * @param string $transactionId
     * @return Successful|Error
     */
    public function void($transactionId)
    {
        return Transaction::void($transactionId);
    }

    /**
     * @param string $transactionId
     * @param null|float $amount
     * @return Successful|Error
     */
    public function refund($transactionId, $amount = null)
    {
        return Transaction::refund($transactionId, $amount);
    }

    /**
     * Clone original transaction
     * @param string $transactionId
     * @param array $attributes
     * @return mixed
     */
    public function cloneTransaction($transactionId, array $attributes)
    {
        return Transaction::cloneTransaction($transactionId, $attributes);
    }

    /**
     * @param $token
     * @return mixed
     */
    public function deletePaymentMethod($token)
    {
        return PaymentMethod::delete($token)->success;
    }

    /**
     * @param $token
     * @param $attribs
     * @return mixed
     */
    public function updatePaymentMethod($token, $attribs)
    {
        return PaymentMethod::update($token, $attribs);
    }

    /**
     * @param $id
     * @return Customer
     * @throws NotFound
     */
    public function getCustomerById($id)
    {
        return Customer::find($id);
    }
}
