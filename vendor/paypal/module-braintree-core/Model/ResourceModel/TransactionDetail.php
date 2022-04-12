<?php

namespace PayPal\Braintree\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TransactionDetail extends AbstractDb
{
    /**
     * Model Initialization
     * @return void
     */
    protected function _construct() // @codingStandardsIgnoreLine
    {
        $this->_init('braintree_transaction_details', 'entity_id');
    }
}
