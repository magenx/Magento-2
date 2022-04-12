<?php

namespace PayPal\Braintree\Model\ResourceModel\CreditPrice;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PayPal\Braintree\Model\CreditPrice;
use PayPal\Braintree\Model\ResourceModel\CreditPrice as CreditPriceResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id'; //@codingStandardsIgnoreLine

    protected function _construct() //@codingStandardsIgnoreLine
    {
        $this->_init(CreditPrice::class, CreditPriceResource::class);
    }
}
