<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model\Adapter;

use Braintree\MultipleValueNode;
use Braintree\RangeNode;
use Braintree\TextNode;
use Braintree\Transaction;
use Braintree\TransactionSearch;

/** @codeCoverageIgnore
 */
class BraintreeSearchAdapter
{
    /**
     * Transaction search Id
     *
     * @return TextNode
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function id(): TextNode
    {
        return TransactionSearch::id();
    }

    /**
     * Merchant Account Id
     *
     * @return MultipleValueNode
     */
    public function merchantAccountId(): MultipleValueNode
    {
        return TransactionSearch::merchantAccountId();
    }

    /**
     * Order Id
     *
     * @return TextNode
     */
    public function orderId(): TextNode
    {
        return TransactionSearch::orderId();
    }

    /**
     * PayPal payment id
     *
     * @return TextNode
     */
    public function paypalPaymentId(): TextNode
    {
        return TransactionSearch::paypalPaymentId();
    }

    /**
     * Created using
     *
     * @return MultipleValueNode
     */
    public function createdUsing(): MultipleValueNode
    {
        return TransactionSearch::createdUsing();
    }

    /**
     * Transaction search type
     *
     * @return MultipleValueNode
     */
    public function type(): MultipleValueNode
    {
        return TransactionSearch::type();
    }

    /**
     * Search transaction by created at
     *
     * @return RangeNode
     */
    public function createdAt(): RangeNode
    {
        return TransactionSearch::createdAt();
    }

    /**
     * Search transaction by amount
     *
     * @return RangeNode
     */
    public function amount(): RangeNode
    {
        return TransactionSearch::amount();
    }

    /**
     * Search transaction by status
     *
     * @return MultipleValueNode
     */
    public function status(): MultipleValueNode
    {
        return TransactionSearch::status();
    }

    /**
     * Settlement batch Id
     *
     * @return TextNode
     */
    public function settlementBatchId(): TextNode
    {
        return TransactionSearch::settlementBatchId();
    }

    /**
     * Payment instrument type
     *
     * @return MultipleValueNode
     */
    public function paymentInstrumentType(): MultipleValueNode
    {
        return TransactionSearch::paymentInstrumentType();
    }
}
