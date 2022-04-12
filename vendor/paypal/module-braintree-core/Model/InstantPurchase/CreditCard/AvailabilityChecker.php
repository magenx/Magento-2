<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model\InstantPurchase\CreditCard;

use PayPal\Braintree\Gateway\Config\Config;
use Magento\InstantPurchase\PaymentMethodIntegration\AvailabilityCheckerInterface;

/**
 * Check availability of Braintree vaulted cards for Instant Purchase
 *
 * Class AvailabilityChecker
 * @package PayPal\Braintree\Model\InstantPurchase\CreditCard
 */
class AvailabilityChecker implements AvailabilityCheckerInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * AvailabilityChecker constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        if ($this->config->isVerify3DSecure()) {
            // Support of 3D secure has not been implemented for instant purchase yet.
            return false;
        }

        return true;
    }
}
