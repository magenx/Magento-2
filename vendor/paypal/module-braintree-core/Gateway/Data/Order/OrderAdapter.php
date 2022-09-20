<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Data\Order;

use PayPal\Braintree\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderAdapter implements OrderAdapterInterface
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var AddressAdapterFactory
     */
    private $addressAdapterFactory;

    /**
     * OrderAdapter constructor.
     * @param Order $order
     * @param CartRepositoryInterface $quoteRepository
     * @param AddressAdapterFactory $addressAdapterFactory
     */
    public function __construct(
        Order $order,
        CartRepositoryInterface $quoteRepository,
        AddressAdapterFactory $addressAdapterFactory
    ) {
        $this->order = $order;
        $this->quoteRepository = $quoteRepository;
        $this->addressAdapterFactory = $addressAdapterFactory;
    }

    /**
     * Returns currency code
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->order->getBaseCurrencyCode();
    }

    /**
     * Returns order increment id
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->order->getIncrementId();
    }

    /**
     * Check whether order is multi shipping
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isMultiShipping()
    {
        $quoteId = $this->order->getQuoteId();
        if (!$quoteId) {
            return false;
        }
        $quote = $this->quoteRepository->get($quoteId);

        return (bool)$quote->getIsMultiShipping();
    }

    /**
     * Returns customer ID
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->order->getCustomerId();
    }

    /**
     * Returns billing address
     *
     * @return AddressAdapterInterface|null
     */
    public function getBillingAddress()
    {
        if ($this->order->getBillingAddress()) {
            return $this->addressAdapterFactory->create(
                ['address' => $this->order->getBillingAddress()]
            );
        }

        return null;
    }

    /**
     * Returns order store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->order->getStoreId();
    }

    /**
     * Returns shipping address
     *
     * @return AddressAdapterInterface|null
     */
    public function getShippingAddress()
    {
        if ($this->order->getShippingAddress()) {
            return $this->addressAdapterFactory->create(
                ['address' => $this->order->getShippingAddress()]
            );
        }

        return null;
    }

    /**
     * Returns order id
     *
     * @return int
     */
    public function getId()
    {
        return $this->order->getEntityId();
    }

    /**
     * Returns order grand total amount
     *
     * @return float|null
     */
    public function getGrandTotalAmount()
    {
        return $this->order->getBaseGrandTotal();
    }

    /**
     * Get base discount amount
     *
     * @return float|null
     */
    public function getBaseDiscountAmount()
    {
        return $this->order->getBaseDiscountAmount();
    }

    /**
     * Gets the remote IP address for the order.
     *
     * @return string|null Remote IP address.
     */
    public function getRemoteIp()
    {
        return $this->order->getRemoteIp();
    }

    /**
     * Get base tax amount
     *
     * @return float|null
     */
    public function getBaseTaxAmount()
    {
        return $this->order->getBaseTaxAmount();
    }

    /**
     * Returns list of line items in the cart
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    public function getItems()
    {
        return $this->order->getItems();
    }
}
