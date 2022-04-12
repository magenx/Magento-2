<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Data;

use Magento\Payment\Gateway\Data\AddressAdapterInterface as MagentoAddressAdapterInterface;

/**
 * Interface AddressAdapterInterface
 * @api
 */
interface AddressAdapterInterface extends MagentoAddressAdapterInterface
{
    /**
     * Gets the street values
     *
     * @return string[]|null
     */
    public function getStreet();
}
