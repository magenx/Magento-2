<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @return TextNode
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function id(): TextNode
    {
        return TransactionSearch::id();
    }

    /**
     * @return MultipleValueNode
     */
    public function merchantAccountId(): MultipleValueNode
    {
        return TransactionSearch::merchantAccountId();
    }

    /**
     * @return TextNode
     */
    public function orderId(): TextNode
    {
        return TransactionSearch::orderId();
    }

    /**
     * @return TextNode
     */
    public function paypalPaymentId(): TextNode
    {
        return TransactionSearch::paypalPaymentId();
    }

    /**
     * @return MultipleValueNode
     */
    public function createdUsing(): MultipleValueNode
    {
        return TransactionSearch::createdUsing();
    }

    /**
     * @return MultipleValueNode
     */
    public function type(): MultipleValueNode
    {
        return TransactionSearch::type();
    }

    /**
     * @return RangeNode
     */
    public function createdAt(): RangeNode
    {
        return TransactionSearch::createdAt();
    }

    /**
     * @return RangeNode
     */
    public function amount(): RangeNode
    {
        return TransactionSearch::amount();
    }

    /**
     * @return MultipleValueNode
     */
    public function status(): MultipleValueNode
    {
        return TransactionSearch::status();
    }

    /**
     * @return TextNode
     */
    public function settlementBatchId(): TextNode
    {
        return TransactionSearch::settlementBatchId();
    }

    /**
     * @return MultipleValueNode
     */
    public function paymentInstrumentType(): MultipleValueNode
    {
        return TransactionSearch::paymentInstrumentType();
    }
}
