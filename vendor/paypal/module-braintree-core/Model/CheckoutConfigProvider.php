<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace PayPal\Braintree\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Adds reCaptcha configuration to checkout.
 */
class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface
     */
    private $isCaptchaEnabled;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        if ($this->moduleManager->isEnabled('Magento_ReCaptchaUi')) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->isCaptchaEnabled = $objectManager->create(
                'Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface'
            );

            return [
                'recaptcha_braintree' => $this->isCaptchaEnabled->isCaptchaEnabledFor('braintree')
            ];
        }

        return [
            'recaptcha_braintree' => false
        ];
    }
}
