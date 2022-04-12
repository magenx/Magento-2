<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model\Paypal\Helper;

use InvalidArgumentException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

class ShippingMethodUpdater extends AbstractHelper
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * Constructor
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Execute operation
     *
     * @param string $shippingMethod
     * @param Quote $quote
     * @return void
     * @throws InvalidArgumentException
     */
    public function execute($shippingMethod, Quote $quote)
    {
        if (empty($shippingMethod)) {
            throw new InvalidArgumentException('The "shippingMethod" field does not exist.');
        }

        if (!$quote->getIsVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingMethod !== $shippingAddress->getShippingMethod()) {
                $this->disabledQuoteAddressValidation($quote);

                $shippingAddress->setShippingMethod($shippingMethod);
                $shippingAddress->setCollectShippingRates(true);

                $cartExtension = $quote->getExtensionAttributes();
                if ($cartExtension && $cartExtension->getShippingAssignments()) {
                    $cartExtension->getShippingAssignments()[0]
                        ->getShipping()
                        ->setMethod($shippingMethod);
                }

                $quote->collectTotals();
                $this->quoteRepository->save($quote);
            }
        }
    }
}
