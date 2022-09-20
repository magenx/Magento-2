<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Block\Customer\PayPal;

use PayPal\Braintree\Gateway\Config\PayPal\Config;
use PayPal\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractTokenRenderer;

/**
 * @api
 * @since 100.0.2
 */
class VaultTokenRenderer extends AbstractTokenRenderer
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Initialize dependencies.
     *
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getIconUrl()
    {
        return $this->config->getPayPalIcon()['url'];
    }

    /**
     * @inheritdoc
     */
    public function getIconHeight()
    {
        return $this->config->getPayPalIcon()['height'];
    }

    /**
     * @inheritdoc
     */
    public function getIconWidth()
    {
        return $this->config->getPayPalIcon()['width'];
    }

    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token): bool
    {
        return $token->getPaymentMethodCode() === ConfigProvider::PAYPAL_CODE;
    }

    /**
     * Get email of PayPal payer
     *
     * @return string
     */
    public function getPayerEmail(): string
    {
        return $this->getTokenDetails()['payerEmail'];
    }
}
