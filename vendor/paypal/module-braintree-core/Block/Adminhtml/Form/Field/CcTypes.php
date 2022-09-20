<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Block\Adminhtml\Form\Field;

use PayPal\Braintree\Helper\CcType;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class CcTypes extends Select
{
    /**
     * @var CcType
     */
    private $ccTypeHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CcType $ccTypeHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CcType $ccTypeHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->ccTypeHelper = $ccTypeHelper;
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->ccTypeHelper->getCcTypes());
        }
        $this->setClass('cc-type-select');
        $this->setExtraParams('multiple="multiple"');
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value . '[]');
    }
}
