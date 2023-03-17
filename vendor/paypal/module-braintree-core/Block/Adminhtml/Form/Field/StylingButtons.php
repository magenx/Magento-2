<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Braintree\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class StylingButtons extends Field
{
    /**
     * @inheritDoc
     */
    protected function _renderScopeLabel(AbstractElement $element): string
    {
        // Return empty label
        return '';
    }

    /**
     * @inheritDoc
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        // Replace field markup with validation button
        $applyTitle = __('Apply');
        $applyAllTitle = __('Apply to All Buttons');
        $resetTitle = __('Reset to Recommended Defaults');
        $applyNote = __(
            ' - This button is responsible to store selected styling field of 
            buttons and pay later messaging for the current location and current button type'
        );
        $applyAllNote = __(
            ' - This button is responsible to store selected styling field of 
            buttons and pay later messaging values for all the Buttons types and locations.'
        );
        $resetDefaults = __(
            ' - This button is responsible to set recommended default values to all the 
            buttons and pay later messaging for all the Buttons types and locations.'
        );

        // @codingStandardsIgnoreStart
        $html = <<<TEXT
            <div class="paypal-styling-buttons">
            <button
                type="button"
                title="{$applyTitle}"
                class="button apply-all-button"
                onclick="applyButton.call(this)">
                <span>{$applyTitle}</span>
            </button>
TEXT;

        $html .= <<<TEXT
            <button
                type="button"
                title="{$applyAllTitle}"
                class="button apply-all-button"
                onclick="applyForAll.call(this)">
                <span>{$applyAllTitle}</span>
            </button>
TEXT;

        $html .= <<<TEXT
            <button
                type="button"
                title="{$resetTitle}"
                class="button reset-to-defaults-button"
                onclick="resetAll.call(this)">
                <span>{$resetTitle}</span>
            </button>
            </div>
TEXT;

        $html .= <<<TEXT
            <p class="note"><span><strong>{$applyTitle}</strong>{$applyNote}</span></p>
            <p class="note"><span><strong>{$applyAllTitle}</strong>{$applyAllNote}</span></p>
            <p class="note"><span><strong>{$resetTitle}</strong>{$resetDefaults}</span></p>
TEXT;
        // @codingStandardsIgnoreEnd

        return $html;
    }
}
