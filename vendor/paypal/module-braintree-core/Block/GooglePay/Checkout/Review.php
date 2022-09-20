<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Block\GooglePay\Checkout;

use Magento\Paypal\Block\Express;

/**
 * @api
 * @since 100.0.2
 */
class Review extends Express\Review
{
    /**
     * @var string
     */
    protected $_controllerPath = 'braintree/googlepay'; // @codingStandardsIgnoreLine
}
