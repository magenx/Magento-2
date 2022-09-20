<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Block;

use PayPal\Braintree\Model\Ui\ConfigProvider;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 * @since 100.1.0
 */
class Payment extends Template
{
    /**
     * @var ConfigProviderInterface
     */
    private $config;

    /**
     * Payment constructor
     *
     * @param Context $context
     * @param ConfigProviderInterface $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigProviderInterface $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * Get Payment Config
     *
     * @return string
     */
    public function getPaymentConfig(): string
    {
        $config = $this->config->getConfig();
        if (isset($config['payment'])) {
            $payment = $config['payment'];
            $config = $payment[$this->getCode()];
        }
        $config['code'] = $this->getCode();

        return json_encode($config, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): string
    {
        return ConfigProvider::CODE;
    }
}
