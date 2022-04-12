<?php

namespace PayPal\Braintree\Api\Data;

/**
 * Interface CreditPriceDataInterface
 **/
interface CreditPriceDataInterface
{
    const ID = 'id';
    const PRODUCT_ID = 'product_id';
    const TERM = 'term';
    const MONTHLY_PAYMENT = 'monthly_payment';
    const INSTALMENT_RATE = 'instalment_rate';
    const COST_OF_PURCHASE = 'cost_of_purchase';
    const TOTAL_INC_INTEREST = 'total_inc_interest';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $value
     * @return self
     */
    public function setId($value): CreditPriceDataInterface;

    /**
     * @return int
     */
    public function getProductId(): int;

    /**
     * @param int $value
     * @return self
     */
    public function setProductId($value): CreditPriceDataInterface;

    /**
     * @return int
     */
    public function getTerm(): int;

    /**
     * @param int $value
     * @return self
     */
    public function setTerm($value): CreditPriceDataInterface;

    /**
     * @return float
     */
    public function getMonthlyPayment(): float;

    /**
     * @param float $value
     * @return self
     */
    public function setMonthlyPayment($value): CreditPriceDataInterface;

    /**
     * @return float
     */
    public function getInstalmentRate(): float;

    /**
     * @param float $value
     * @return self
     */
    public function setInstalmentRate($value): CreditPriceDataInterface;

    /**
     * @return float
     */
    public function getCostOfPurchase(): float;

    /**
     * @param float $value
     * @return self
     */
    public function setCostOfPurchase($value): CreditPriceDataInterface;

    /**
     * @return float
     */
    public function getTotalIncInterest(): float;

    /**
     * @param float $value
     * @return self
     */
    public function setTotalIncInterest($value): CreditPriceDataInterface;
}
