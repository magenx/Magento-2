<?php

namespace PayPal\Braintree\Model;

use PayPal\Braintree\Api\Data\CreditPriceDataInterface;
use Magento\Framework\Model\AbstractModel;
use PayPal\Braintree\Model\ResourceModel\CreditPrice as CreditPriceResource;

class CreditPrice extends AbstractModel implements CreditPriceDataInterface
{
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct() // @codingStandardsIgnoreLine
    {
        $this->_init(CreditPriceResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function getProductId(): int
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function getTerm(): int
    {
        return $this->getData(self::TERM);
    }

    /**
     * @inheritdoc
     */
    public function getMonthlyPayment(): float
    {
        return $this->getData(self::MONTHLY_PAYMENT);
    }

    /**
     * @inheritdoc
     */
    public function getInstalmentRate(): float
    {
        return $this->getData(self::INSTALMENT_RATE);
    }

    /**
     * @inheritdoc
     */
    public function getCostOfPurchase(): float
    {
        return $this->getData(self::COST_OF_PURCHASE);
    }

    /**
     * @inheritdoc
     */
    public function getTotalIncInterest(): float
    {
        return $this->getData(self::TOTAL_INC_INTEREST);
    }

    /**
     * @inheritdoc
     */
    public function setId($value): CreditPriceDataInterface
    {
        return $this->setData(self::ID, $value);
    }

    /**
     * @inheritdoc
     */
    public function setProductId($value): CreditPriceDataInterface
    {
        return $this->setData(self::PRODUCT_ID, $value);
    }

    /**
     * @inheritdoc
     */
    public function setTerm($value): CreditPriceDataInterface
    {
        return $this->setData(self::TERM, $value);
    }

    /**
     * @inheritdoc
     */
    public function setMonthlyPayment($value): CreditPriceDataInterface
    {
        return $this->setData(self::MONTHLY_PAYMENT, $value);
    }

    /**
     * @inheritdoc
     */
    public function setInstalmentRate($value): CreditPriceDataInterface
    {
        return $this->setData(self::INSTALMENT_RATE, $value);
    }

    /**
     * @inheritdoc
     */
    public function setCostOfPurchase($value): CreditPriceDataInterface
    {
        return $this->setData(self::COST_OF_PURCHASE, $value);
    }

    /**
     * @inheritdoc
     */
    public function setTotalIncInterest($value): CreditPriceDataInterface
    {
        return $this->setData(self::TOTAL_INC_INTEREST, $value);
    }
}
