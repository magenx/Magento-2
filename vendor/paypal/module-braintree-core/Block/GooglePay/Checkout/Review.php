<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Block\GooglePay\Checkout;

use Magento\Paypal\Block\Express;

class Review extends Express\Review
{
    /**
     * @var string
     */
    protected $_controllerPath = 'braintree/googlepay'; // @codingStandardsIgnoreLine
}
