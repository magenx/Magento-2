/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Checkout/js/model/payment/additional-validators',
    'mage/translate'
], function ($, additionalValidators, $t) {
    'use strict';

    return function (originalComponent) {
        return originalComponent.extend({
            /**
             * Initializes reCaptcha
             */
            placeOrder: function () {
                var original = this._super.bind(this),
                    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    isEnabled = window.checkoutConfig.recaptcha_braintree,
                    // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                    paymentFormSelector = $('#co-payment-form'),
                    startEvent = 'captcha:startExecute',
                    endEvent = 'captcha:endExecute';

                if (!additionalValidators.validate() || !isEnabled || this.getCode() !== 'braintree') {
                    return original();
                }

                paymentFormSelector.off(endEvent).on(endEvent, function () {
                    var recaptchaCheckBox = jQuery("#recaptcha-checkout-braintree-wrapper input[name='recaptcha-validate-']");

                    if (recaptchaCheckBox.length && recaptchaCheckBox.prop('checked') === false) {
                        alert($t('Please indicate google recaptcha'));
                    } else {
                        original();
                        paymentFormSelector.off(endEvent);
                    }
                });

                paymentFormSelector.trigger(startEvent);
            }
        });
    };
});
