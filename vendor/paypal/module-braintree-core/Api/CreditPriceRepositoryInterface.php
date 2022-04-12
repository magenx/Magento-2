<?php

namespace PayPal\Braintree\Api;

use PayPal\Braintree\Api\Data\CreditPriceDataInterface;
use Magento\Framework\DataObject;

/**
 * Interface CreditPricesInterface
 * @api
 **/
interface CreditPriceRepositoryInterface
{
    /**
     * @param \PayPal\Braintree\Api\Data\CreditPriceDataInterface $entity
     * @return \PayPal\Braintree\Api\Data\CreditPriceDataInterface
     */
    public function save(CreditPriceDataInterface $entity): CreditPriceDataInterface;

    /**
     * @param int $productId
     * @return \PayPal\Braintree\Api\Data\CreditPriceDataInterface
     */
    public function getByProductId($productId);

    /**
     * @param $productId
     * @return \PayPal\Braintree\Api\Data\CreditPriceDataInterface|\Magento\Framework\DataObject
     */
    public function getCheapestByProductId($productId);

    /**
     * @param int $productId
     * @return \PayPal\Braintree\Api\Data\CreditPriceDataInterface[]
     */
    public function deleteByProductId($productId);
}
