<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model\Report\ConditionAppliers;

use Braintree\RangeNode;

/**
 * Range applier
 */
class Range implements ApplierInterface
{
    /**
     * @inheritDoc
     */
    public function apply($field, $condition, $value): bool
    {
        $result = false;

        switch ($condition) {
            case ApplierInterface::QTEQ:
                $field->greaterThanOrEqualTo($value);
                $result = true;
                break;
            case ApplierInterface::LTEQ:
                $field->lessThanOrEqualTo($value);
                $result = true;
                break;
        }

        return $result;
    }
}
