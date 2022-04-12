<?php
declare(strict_types=1);

namespace PayPal\Braintree\Model\Adminhtml\Source;

use PayPal\Braintree\Model\Lpm\Config;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Provide options for backend config.
 */
class LpmMethods implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => Config::VALUE_BANCONTACT, 'label' => __('Bancontact')],
            ['value' => Config::VALUE_EPS, 'label' => __('EPS')],
            ['value' => Config::VALUE_GIROPAY, 'label' => __('giropay')],
            ['value' => Config::VALUE_IDEAL, 'label' => __('iDeal')],
            ['value' => Config::VALUE_SOFORT, 'label' => __('Klarna Pay Now / SOFORT')],
            ['value' => Config::VALUE_MYBANK, 'label' => __('MyBank')],
            ['value' => Config::VALUE_P24, 'label' => __('P24')],
            ['value' => Config::VALUE_SEPA, 'label' => __('SEPA/ELV Direct Debit')]
        ];
    }
}
