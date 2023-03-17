<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Data\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use PayPal\Braintree\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderAdapter implements OrderAdapterInterface
{
    /**
     * @var Order
     */
    private Order $order;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @var AddressAdapterFactory
     */
    private AddressAdapterFactory $addressAdapterFactory;

    /**
     * OrderAdapter constructor.
     *
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
    public function getCurrencyCode(): string
    {
        return $this->order->getBaseCurrencyCode();
    }

    /**
     * Returns order increment id
     *
     * @return string
     */
    public function getOrderIncrementId(): string
    {
        return $this->order->getIncrementId();
    }

    /**
     * Check whether order is multi shipping
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isMultiShipping(): bool
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
    public function getCustomerId(): ?int
    {
        return $this->order->getCustomerId();
    }

    /**
     * Returns billing address
     *
     * @return AddressAdapterInterface|null
     */
    public function getBillingAddress(): ?AddressAdapterInterface
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
    public function getStoreId(): int
    {
        return $this->order->getStoreId();
    }

    /**
     * Returns shipping address
     *
     * @return AddressAdapterInterface|null
     */
    public function getShippingAddress(): ?AddressAdapterInterface
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
    public function getId(): int
    {
        return $this->order->getEntityId();
    }

    /**
     * Returns order grand total amount
     *
     * @return float|null
     */
    public function getGrandTotalAmount(): ?float
    {
        return $this->order->getBaseGrandTotal();
    }

    /**
     * Get base discount amount
     *
     * @return float|null
     */
    public function getBaseDiscountAmount(): ?float
    {
        return $this->order->getBaseDiscountAmount();
    }

    /**
     * Gets the remote IP address for the order.
     *
     * @return string|null Remote IP address.
     */
    public function getRemoteIp(): ?string
    {
        return $this->order->getRemoteIp();
    }

    /**
     * Get base tax amount
     *
     * @return float|null
     */
    public function getBaseTaxAmount(): ?float
    {
        return $this->order->getBaseTaxAmount();
    }

    /**
     * Returns list of line items in the cart
     *
     * @return OrderItemInterface[]
     */
    public function getItems(): array
    {
        return $this->order->getItems();
    }

    /**
     * Retrieve existing extension attributes object
     *
     * @return OrderExtensionInterface|null
     */
    public function getExtensionAttributes(): ?OrderExtensionInterface
    {
        return $this->order->getExtensionAttributes();
    }

    /**
     * Return quote_id
     *
     * @return int|null
     */
    public function getQuoteId(): ?int
    {
        return $this->order->getQuoteId();
    }
}
