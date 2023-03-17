<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Observer;

use PayPal\Braintree\Block\Paypal\Button;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Checkout\Block\QuoteShortcutButtons;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use PayPal\Braintree\Gateway\Config\PayPal\Config as PayPalConfig;

class AddPaypalShortcuts implements ObserverInterface
{
    /**
     * Block class
     */
    public const PAYPAL_SHORTCUT_BLOCK = Button::class;

    /**
     * @var PayPalConfig
     */
    private $payPalConfig;

    /**
     * @param PayPalConfig $config
     */
    public function __construct(
        PayPalConfig $config
    ) {
        $this->payPalConfig = $config;
    }

    /**
     * Add Braintree PayPal shortcut buttons
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        if (!$this->payPalConfig->isActive()) {
            return;
        }
        // Remove button from catalog pages
        if ($observer->getData('is_catalog_product')) {
            return;
        }

        /** @var ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        $shortcut = $shortcutButtons->getLayout()->createBlock(self::PAYPAL_SHORTCUT_BLOCK);
        $shortcut->setIsCart(get_class($shortcutButtons) === QuoteShortcutButtons::class);
        $shortcutButtons->addShortcut($shortcut);
    }
}
