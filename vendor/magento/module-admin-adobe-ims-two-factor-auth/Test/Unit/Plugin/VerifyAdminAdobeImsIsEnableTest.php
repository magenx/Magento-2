<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeImsTwoFactorAuth\Test\Unit\Plugin;

use Magento\Framework\Controller\ResultInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\AdminAdobeImsTwoFactorAuth\Plugin\VerifyAdminAdobeImsIsEnable;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\TwoFactorAuth\Observer\ControllerActionPredispatch;
use Magento\Framework\Event\Observer;

/**
 * Test coverage for Plugin to verify whether AdminAdobeIms Module is enable or not before 2FA
 */
class VerifyAdminAdobeImsIsEnableTest extends TestCase
{
    /** @var ControllerActionPredispatch|MockObject */
    private ControllerActionPredispatch $controllerActionPredispatch;

    /** @var ImsConfig|MockObject */
    private ImsConfig $imsConfig;

    /** @var Observer|MockObject */
    private Observer $observer;

    /** @var VerifyAdminAdobeImsIsEnable */
    private VerifyAdminAdobeImsIsEnable $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->controllerActionPredispatch = $this->createMock(ControllerActionPredispatch::class);
        $this->imsConfig = $this->createMock(ImsConfig::class);
        $this->observer = $this->createMock(Observer::class);
        $this->plugin = new VerifyAdminAdobeImsIsEnable($this->imsConfig);
    }

    /**
     * Test when AdobeIms is Enabled
     *
     * @return void
     */
    public function testAroundExecuteWhenAdobeImsIsEnabled(): void
    {
        $closure = function () {
            return;
        };

        $this->imsConfig
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(true);

        $this->assertEmpty(
            $this->plugin->aroundExecute(
                $this->controllerActionPredispatch,
                $closure,
                $this->observer
            )
        );
    }

    /**
     * Test when AdobeIms is Disabled
     *
     * @return void
     */
    public function testAroundExecuteWhenAdobeImsIsDisabled(): void
    {
        $result = $this->getMockBuilder(ResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $closure = function () use ($result) {
            return $result;
        };

        $this->imsConfig
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(false);

        $this->assertEquals(
            $result,
            $this->plugin->aroundExecute(
                $this->controllerActionPredispatch,
                $closure,
                $this->observer
            )
        );
    }
}
