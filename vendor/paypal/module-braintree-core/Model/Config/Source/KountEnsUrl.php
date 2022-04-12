<?php
declare(strict_types=1);

namespace PayPal\Braintree\Model\Config\Source;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Displays Kount ENS endpoint URL in config.
 */
class KountEnsUrl extends Field
{
    const ENS_URL = 'braintree/kount/ens';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * KountEnsUrl constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritDoc}
     * @param AbstractElement $element
     * @return string
     */
    public function _getElementHtml(AbstractElement $element): string
    {
        $baseUrl = $this->scopeConfig->getValue('web/secure/base_url');
        return $baseUrl . self::ENS_URL;
    }
}
