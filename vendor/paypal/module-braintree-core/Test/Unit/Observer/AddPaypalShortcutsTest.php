<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Test\Unit\Observer;

use PayPal\Braintree\Block\Paypal\Button;
use PayPal\Braintree\Gateway\Config\PayPal\Config;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use PayPal\Braintree\Observer\AddPaypalShortcuts;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @see \PayPal\Braintree\Observer\AddPaypalShortcuts
 */
class AddPaypalShortcutsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config|MockObject
     */
    private $config;

    public function testExecute()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $addPaypalShortcuts = new AddPaypalShortcuts($this->config);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Event|MockObject $eventMock */
        $eventMock = $this->getMockBuilder(Event::class)
            ->setMethods(['getContainer'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ShortcutButtons|MockObject $shortcutButtonsMock */
        $shortcutButtonsMock = $this->getMockBuilder(ShortcutButtons::class)
            ->disableOriginalConstructor()
            ->getMock();

        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();

        $blockMock = $this->getMockBuilder(Button::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->method('isActive')
            ->willReturn(true);

        $observerMock->expects(self::once())
            ->method('getEvent')
            ->willReturn($eventMock);

        $eventMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($shortcutButtonsMock);

        $shortcutButtonsMock->expects(self::once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $layoutMock->expects(self::once())
            ->method('createBlock')
            ->with(AddPaypalShortcuts::PAYPAL_SHORTCUT_BLOCK)
            ->willReturn($blockMock);

        $shortcutButtonsMock->expects(self::once())
            ->method('addShortcut')
            ->with($blockMock);

        $addPaypalShortcuts->execute($observerMock);
    }
}
