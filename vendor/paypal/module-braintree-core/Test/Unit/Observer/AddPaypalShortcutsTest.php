<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Test\Unit\Observer;

use PayPal\Braintree\Block\Paypal\Button;
use PayPal\Braintree\Gateway\Config\Config;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use PayPal\Braintree\Model\Paypal\OrderCancellationService;
use PayPal\Braintree\Observer\AddPaypalShortcuts;
use Magento\Framework\View\LayoutInterface;

/**
 * @see \PayPal\Braintree\Observer\AddPaypalShortcuts
 */
class AddPaypalShortcutsTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $addPaypalShortcuts = new AddPaypalShortcuts($this->config);

        /** @var Observer|\PHPUnit\Framework\MockObject\MockObject $observerMock */
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Event|\PHPUnit\Framework\MockObject\MockObject $eventMock */
        $eventMock = $this->getMockBuilder(Event::class)
            ->setMethods(['getContainer'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ShortcutButtons|\PHPUnit\Framework\MockObject\MockObject $shortcutButtonsMock */
        $shortcutButtonsMock = $this->getMockBuilder(ShortcutButtons::class)
            ->disableOriginalConstructor()
            ->getMock();

        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();

        $blockMock = $this->getMockBuilder(Button::class)
            ->disableOriginalConstructor()
            ->getMock();

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
