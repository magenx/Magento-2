<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Braintree\Model\Webhook;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PayPal\Braintree\Gateway\Helper\SubjectReader;

class Config
{
    private const WEBHOOK_ENABLED = 'payment/braintree_webhook/enabled';
    private const WEBHOOK_FRAUD_PROTECTION_URL = 'payment/braintree_webhook/fraud_protection_url';
    private const WEBHOOK_APPROVE_ORDER_STATUS = 'payment/braintree_webhook/approve_order_status';
    private const WEBHOOK_REJECT_ORDER_STATUS = 'payment/braintree_webhook/reject_order_status';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor
     *
     * @param SubjectReader $subjectReader
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SubjectReader $subjectReader,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->subjectReader = $subjectReader;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is webhook enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(int $storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::WEBHOOK_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Fraud Protection URL
     *
     * @param int|null $storeId
     * @return string
     */
    public function getFraudProtectionUrl(int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::WEBHOOK_FRAUD_PROTECTION_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get fraud protection approve order status
     *
     * @param int|null $storeId
     * @return string
     */
    public function getFraudApproveOrderStatus(int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::WEBHOOK_APPROVE_ORDER_STATUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get fraud protection reject order status
     *
     * @param int|null $storeId
     * @return string
     */
    public function getFraudRejectOrderStatus(int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::WEBHOOK_REJECT_ORDER_STATUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
