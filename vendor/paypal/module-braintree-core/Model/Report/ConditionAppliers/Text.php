<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model\Report\ConditionAppliers;

use Braintree\TextNode;

/**
 * Text applier
 */
class Text implements ApplierInterface
{
    /**
     * @inheritDoc
     */
    public function apply($field, $condition, $value): bool
    {
        $result = false;

        $value = trim($value, "% \r\n\t");
        switch ($condition) {
            case ApplierInterface::EQ:
                $field->is($value);
                $result = true;
                break;
            case ApplierInterface::LIKE:
                $field->contains($value);
                $result = true;
                break;
        }

        return $result;
    }
}
