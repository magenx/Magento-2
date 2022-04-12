<?php

namespace PayPal\Braintree\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CreditPrice extends AbstractDb
{
    public function _construct() //@codingStandardsIgnoreLine
    {
        $this->_init('braintree_credit_prices', 'id');
    }
}
