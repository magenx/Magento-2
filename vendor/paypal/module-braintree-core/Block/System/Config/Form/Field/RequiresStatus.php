<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Block\System\Config\Form\Field;

class RequiresStatus extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Reset 'Requires' CSS Class
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if (str_contains($element->getClass(), 'braintree_ach_direct_debit_')) {
            $requiresClass = str_replace('braintree_ach_direct_debit_', '', $element->getClass());
            $element->setClass($requiresClass);
        }
        if (str_contains($element->getClass(), 'braintree_applepay_')) {
            $requiresClass = str_replace('braintree_applepay_', '', $element->getClass());
            $element->setClass($requiresClass);
        }
        if (str_contains($element->getClass(), 'braintree_local_payment_')) {
            $requiresClass = str_replace('braintree_local_payment_', '', $element->getClass());
            $element->setClass($requiresClass);
        }
        if (str_contains($element->getClass(), 'braintree_googlepay_')) {
            $requiresClass = str_replace('braintree_googlepay_', '', $element->getClass());
            $element->setClass($requiresClass);
        }
        if (str_contains($element->getClass(), 'braintree_venmo_')) {
            $requiresClass = str_replace('braintree_venmo_', '', $element->getClass());
            $element->setClass($requiresClass);
        }

        return parent::render($element);
    }
}
