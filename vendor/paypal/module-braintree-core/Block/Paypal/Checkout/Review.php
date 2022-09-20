<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Block\Paypal\Checkout;

use Magento\Paypal\Block\Express;

/**
 * @api
 * @since 100.1.0
 */
class Review extends Express\Review
{
    /**
     *  Path for Controller
     *
     * @var string
     * @since 100.1.0
     */
    protected $_controllerPath = 'braintree/paypal'; // @codingStandardsIgnoreLine

    /**
     * Does not allow editing payment information as customer has gone through paypal flow already
     *
     * @return null
     * @codeCoverageIgnore
     * @since 100.1.0
     */
    public function getEditUrl()
    {
        return null;
    }
}
