<?php

namespace PayPal\Braintree\Block\Adminhtml\Virtual;

use Magento\Backend\Block\Widget\Form\Container;

class Form extends Container
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_blockGroup = 'PayPal_Braintree';
        $this->_controller = 'adminhtml_virtual';
        parent::_construct();

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('save');
        $this->addButton(
            'save',
            [
                'label' => __('Take Payment'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'takePayment', 'target' => '#payment_form_braintree']],
                ]
            ],
            1
        );
    }
}
