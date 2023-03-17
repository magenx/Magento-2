<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeImsTwoFactorAuth\Plugin;

use Magento\TwoFactorAuth\Observer\ControllerActionPredispatch;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Event\Observer;

/**
 * Plugin to verify whether AdminAdobeIms Module is enable or not before 2FA
 */
class VerifyAdminAdobeImsIsEnable
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $adminAdobeImsConfig;

    /**
     * @param ImsConfig $adminAdobeImsConfig
     */
    public function __construct(
        ImsConfig $adminAdobeImsConfig
    ) {
        $this->adminAdobeImsConfig = $adminAdobeImsConfig;
    }

    /**
     * Verify whether AdminAdobeIMS Module is enabled
     *
     * @param ControllerActionPredispatch $subject
     * @param callable $proceed
     * @param Observer $observer
     * @return ControllerActionPredispatch|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        ControllerActionPredispatch $subject,
        callable $proceed,
        Observer $observer
    ) {
        if ($this->adminAdobeImsConfig->enabled()) {
            return;
        }

        return $proceed($observer);
    }
}
