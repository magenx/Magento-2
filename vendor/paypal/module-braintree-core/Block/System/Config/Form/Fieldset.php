<?php

namespace PayPal\Braintree\Block\System\Config\Form;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use PayPal\Braintree\Gateway\Config\PayPalCredit\Config;
use Magento\Config\Model\Config as backendConfig;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;
use Magento\Paypal\Block\Adminhtml\System\Config\Fieldset\Payment;

class Fieldset extends Payment
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Fieldset constructor
     *
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param backendConfig $backendConfig
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        backendConfig $backendConfig,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $backendConfig, $data);
        $this->config = $config;
    }

    /**
     * Remove US & UK specific fields from the form if merchant country is not UK or US.
     *
     * @inheritDoc
     */
    protected function _getChildrenElementsHtml(AbstractElement $element): string // @codingStandardsIgnoreLine
    {
        $countryCode = $this->getRequest()->getParam('paypal_country');
        if ($countryCode) {
            $locale = $countryCode;
        } else {
            $locale = $this->config->getMerchantCountry();
        }
        if (is_string($locale)) {
            $locale = strtolower($locale);
        }

        // Only available to GB
        if ($locale !== 'gb') {
            $element->removeField(
                'payment_' . $locale . '_braintree_section_braintree_braintree_paypal_credit'
            );
            $element->removeField(
                'payment_other_braintree_section_braintree_braintree_paypal_credit'
            );
        }

        // Only available to GB and US
        if ($locale !== 'gb' && $locale !== 'us') {
            $element->removeField(
                'payment_' . $locale . '_braintree_section_braintree_braintree_paypal_credit_active'
            );
            $element->removeField(
                'payment_other_braintree_section_braintree_braintree_paypal_credit_active'
            );
        }

        return parent::_getChildrenElementsHtml($element);
    }
}
