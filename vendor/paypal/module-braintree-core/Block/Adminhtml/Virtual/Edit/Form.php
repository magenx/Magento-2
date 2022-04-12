<?php

namespace PayPal\Braintree\Block\Adminhtml\Virtual\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Exception\LocalizedException;

class Form extends Generic
{
    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm(): Generic
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
                'data' => [
                    'id' => 'payment_form_braintree',
                    'action' => $this->getUrl('braintree/*/save'),
                    'method' => 'post',
                ],
            ]);

        $fieldset = $form->addFieldset('transaction_details', []);
        $fieldset->addField('payment_method_nonce', 'hidden', [
            'name' => 'payment_method_nonce'
        ]);

        $fieldset->addField(
            'amount',
            'text',
            [
                'label' => __('Amount'),
                'required' => true,
                'name' => 'amount',
                'placeholder' => '00.00',
                'class' => 'not-negative-amount validate-greater-than-zero validate-number'
            ]
        );

        $fieldset->addField(
            'braintree_cc_number_container',
            'note',
            [
                'label' => __('Card Number'),
                'required' => true,
                'text' => <<<'TEXT'
<div class="admin__control-text hosted-control hosted-control" id="braintree_cc_number"></div>
TEXT
            ]
        );

        $fieldset->addField(
            'braintree_cc_exp_container',
            'note',
            [
                'label' => __('Expiration Date'),
                'required' => true,
                'text' => <<<'TEXT'
<div class="hosted-date-wrap">
    <div class="admin__control-text hosted-control hosted-date" id="braintree_cc_exp_month"></div>
    <div class="admin__control-text hosted-control hosted-date" id="braintree_cc_exp_year"></div>
</div>
TEXT
            ]
        );

        $fieldset->addField(
            'braintree_cc_cid_container',
            'note',
            [
                'label' => __('Card Verification Number'),
                'required' => true,
                'text' => <<<'TEXT'
<div class="admin__control-text hosted-control hosted-cid" id="braintree_cc_cid"></div>
TEXT
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
