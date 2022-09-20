<?php

namespace PayPal\Braintree\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class SalesOrderGridPlugin
{
    /**
     * @param Collection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     * @throws LocalizedException
     */
    public function beforeLoad(Collection $subject, bool $printQuery = false, bool $logQuery = false): array
    {
        if (!$subject->isLoaded()) {
            $primaryKey = $subject->getResource()->getIdFieldName();
            $tableName = $subject->getResource()->getTable('braintree_transaction_details');

            $subject->getSelect()->joinLeft(
                $tableName,
                $tableName . '.order_id = main_table.' . $primaryKey,
                $tableName . '.transaction_source'
            );
        }

        return [$printQuery, $logQuery];
    }
}
