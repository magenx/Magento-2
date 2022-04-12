<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model\Report\Row;

use Braintree\Transaction;
use DateTime;
use LogicException;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\Search\DocumentInterface;
use stdClass;

class TransactionMap implements DocumentInterface
{
    const TRANSACTION_FIELD_MAP_DELIMITER = '_';

    /**
     * @var AttributeValueFactory
     */
    private $attributeValueFactory;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var array
     */
    public static $simpleFieldsMap = [
        'id',
        'merchantAccountId',
        'orderId',
        'paymentInstrumentType',
        'paypalDetails_paymentId',
        'type',
        'createdAt',
        'amount',
        'processorSettlementResponseCode',
        'status',
        'processorSettlementResponseText',
        'refundIds',
        'settlementBatchId',
        'currencyIsoCode'
    ];

    /**
     * @param AttributeValueFactory $attributeValueFactory
     * @param Transaction $transaction
     */
    public function __construct(
        AttributeValueFactory $attributeValueFactory,
        Transaction $transaction
    ) {
        $this->attributeValueFactory = $attributeValueFactory;
        $this->transaction = $transaction;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->getMappedValue('id');
    }

    /**
     * Set Id
     *
     * @param int $id
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setId($id)
    {
        return null;
    }

    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return AttributeInterface|null
     */
    public function getCustomAttribute($attributeCode)
    {
        /** @var AttributeInterface $attributeValue */
        $attributeValue = $this->attributeValueFactory->create();
        $attributeValue->setAttributeCode($attributeCode);
        $attributeValue->setValue($this->getMappedValue($attributeCode));
        return $attributeValue;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        return $this;
    }

    /**
     * Retrieve custom attributes values.
     *
     * @return AttributeInterface[]|null
     */
    public function getCustomAttributes()
    {
        $shouldBeLocalized = ['paymentInstrumentType', 'type', 'status'];
        $output = [];
        foreach ($this->getMappedValues() as $key => $value) {
            $attribute = $this->attributeValueFactory->create();
            if (in_array($key, $shouldBeLocalized)) {
                $value = __($value);
            }
            $output[] = $attribute->setAttributeCode($key)->setValue($value);
        }
        return $output;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setCustomAttributes(array $attributes)
    {
        return $this;
    }

    /**
     * Get mapped value
     *
     * @param string $key
     * @return mixed
     */
    private function getMappedValue($key)
    {
        if (!in_array($key, static::$simpleFieldsMap)) {
            return null;
        }

        $val = $this->getTransactionFieldValue($key);
        $val = $this->convertToText($val);
        return $val;
    }

    /**
     * @return array
     */
    private function getMappedValues(): array
    {
        $result = [];

        foreach (static::$simpleFieldsMap as $key) {
            $val = $this->getTransactionFieldValue($key);
            $val = $this->convertToText($val);
            $result[$key] = $val;
        }

        return $result;
    }

    /**
     * Recursive get transaction field value
     *
     * @param string $key
     * @return Transaction|mixed|null
     */
    private function getTransactionFieldValue($key)
    {
        $keys = explode(self::TRANSACTION_FIELD_MAP_DELIMITER, $key);
        $result = $this->transaction;
        foreach ($keys as $k) {
            if (!isset($result->$k)) {
                $result = null;
                break;
            }
            $result = $result->$k;
        }
        return $result;
    }

    /**
     * Convert value to text representation
     *
     * @param string|array|stdClass $val
     * @return string
     */
    private function convertToText($val): string
    {
        if (is_object($val)) {
            $i = get_class($val);
            if ($i === 'DateTime') {
                /** @var DateTime $val */
                $val = $val->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            }
        } elseif (is_array($val)) {
            $val = implode(', ', $val);
        }

        return (string) $val;
    }
}
