/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'underscore',
    'braintreeCheckoutPayPalAdapter',
    'Magento_Checkout/js/model/quote',
    'PayPal_Braintree/js/view/payment/method-renderer/paypal',
    'Magento_Checkout/js/action/set-payment-information',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/translate'
], function (
    $,
    _,
    Braintree,
    quote,
    Component,
    setPaymentInformationAction,
    additionalValidators,
    fullScreenLoader,
    $t
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PayPal_Braintree/payment/multishipping/paypal',
            submitButtonSelector: '[id="parent-payment-continue"]',
            reviewButtonHtml: ''
        },

        /**
         * @override
         */
        initObservable: function () {
            this.reviewButtonHtml = $(this.submitButtonSelector).html();
            return this._super();
        },

        initClientConfig: function () {
            this.clientConfig = _.extend(this.clientConfig, this.getPayPalConfig());
            this.clientConfig.paypal.enableShippingAddress = false;

            _.each(this.clientConfig, function (fn, name) {
                if (typeof fn === 'function') {
                    this.clientConfig[name] = fn.bind(this);
                }
            }, this);
            this.clientConfig.buttonPayPalId = 'parent-payment-continue';

        },

        /**
         * @override
         */
        onActiveChange: function (isActive) {
            this.updateSubmitButtonHtml(isActive);
            this._super(isActive);
        },

        /**
         * @override
         */
        beforePlaceOrder: function (data) {
            this._super(data);
        },

        /**
         * Re-init PayPal Auth Flow
         */
        reInitPayPal: function () {
            this.disableButton();
            this.clientConfig.paypal.amount = parseFloat(this.grandTotalAmount).toFixed(2);

            if (!quote.isVirtual()) {
                this.clientConfig.paypal.enableShippingAddress = false;
                this.clientConfig.paypal.shippingAddressEditable = false;
            }

            Braintree.setConfig(this.clientConfig);

            if (Braintree.getPayPalInstance()) {
                Braintree.getPayPalInstance().teardown(function () {
                    Braintree.setup();
                }.bind(this));
                Braintree.setPayPalInstance(null);
            } else {
                Braintree.setup();
                this.enableButton();
            }
        },

        loadPayPalButton: function (paypalCheckoutInstance, funding) {
            let paypalPayment = Braintree.config.paypal,
                onPaymentMethodReceived = Braintree.config.onPaymentMethodReceived;
            let style = {
                color: Braintree.getColor(funding),
                shape: Braintree.getShape(funding),
                size: Braintree.getSize(funding),
                label: Braintree.getLabel(funding)
            };

            if (Braintree.getBranding()) {
                style.branding = Braintree.getBranding();
            }
            if (Braintree.getFundingIcons()) {
                style.fundingicons = Braintree.getFundingIcons();
            }

            if (funding === 'credit') {
                Braintree.config.buttonId = this.clientConfig.buttonCreditId;
            } else if (funding === 'paylater') {
                Braintree.config.buttonId = this.clientConfig.buttonPaylaterId;
            } else {
                Braintree.config.buttonId = this.clientConfig.buttonPayPalId;
            }

            // Render
            Braintree.config.paypalInstance = paypalCheckoutInstance;
            var events = Braintree.events;
            $('#' + Braintree.config.buttonId).html('');

            var button = paypal.Buttons({
                fundingSource: funding,
                env: Braintree.getEnvironment(),
                style: style,
                commit: true,
                locale: Braintree.config.paypal.locale,

                createOrder: function () {
                    return paypalCheckoutInstance.createPayment(paypalPayment);
                },

                onCancel: function (data) {
                    console.log('checkout.js payment cancelled', JSON.stringify(data, 0, 2));

                    if (typeof events.onCancel === 'function') {
                        events.onCancel();
                    }
                },

                onError: function (err) {
                    Braintree.showError($t("PayPal Checkout could not be initialized. Please contact the store owner."));
                    Braintree.config.paypalInstance = null;
                    console.error('Paypal checkout.js error', err);

                    if (typeof events.onError === 'function') {
                        events.onError(err);
                    }
                }.bind(this),

                onClick: function (data) {
                    // To check term & conditions input checked - validate additional validators.
                    if (!additionalValidators.validate()) {
                        return false;
                    }

                    if (typeof events.onClick === 'function') {
                        events.onClick(data);
                    }
                }.bind(this),

                onApprove: function (data, actions) {
                    return paypalCheckoutInstance.tokenizePayment(data)
                        .then(function (payload) {
                            onPaymentMethodReceived(payload);
                        });
                }

            });
            if (button.isEligible() && $('#' + Braintree.config.buttonId).length) {

                button.render('#' + Braintree.config.buttonId).then(function () {
                    Braintree.enableButton();
                    if (typeof Braintree.config.onPaymentMethodError === 'function') {
                        Braintree.config.onPaymentMethodError();
                    }
                }.bind(this)).then(function (data) {
                    if (typeof events.onRender === 'function') {
                        events.onRender(data);
                    }
                });
            }
        },

        /**
         * Get configuration for PayPal
         * @returns {Object}
         */
        getPayPalConfig: function () {
            var totals = quote.totals(),
                config = {},
                isActiveVaultEnabler = this.isActiveVault();

            config.paypal = {
                flow: 'checkout',
                amount: parseFloat(this.grandTotalAmount).toFixed(2),
                currency: totals['base_currency_code'],
                locale: this.getLocale(),
                requestBillingAgreement: true,
                /**
                 * Triggers on any Braintree error
                 */
                onError: function () {
                    this.paymentMethodNonce = null;
                },

                /**
                 * Triggers if browser doesn't support PayPal Checkout
                 */
                onUnsupported: function () {
                    this.paymentMethodNonce = null;
                }
            };

            if (!quote.isVirtual()) {
                config.paypal.enableShippingAddress = false;
                config.paypal.shippingAddressEditable = false;
            }

            if (this.getMerchantName()) {
                config.paypal.displayName = this.getMerchantName();
            }

            return config;
        },

        getShippingAddress: function () {

            return {};
        },

        /**
         * @override
         */
        getData: function () {
            var data = this._super();

            data['additional_data']['is_active_payment_token_enabler'] = true;

            return data;
        },

        /**
         * @override
         */
        isActiveVault: function () {
            return true;
        },

        /**
         * Skipping order review step on checkout with multiple addresses is not allowed.
         *
         * @returns {Boolean}
         */
        isSkipOrderReview: function () {
            return false;
        },

        /**
         * Checks if payment method nonce is already received.
         *
         * @returns {Boolean}
         */
        isPaymentMethodNonceReceived: function () {
            return this.paymentMethodNonce !== null;
        },

        /**
         * Update submit button on multi-addresses checkout billing form.
         *
         * @param {Boolean} isActive
         */
        updateSubmitButtonHtml: function (isActive) {
            $(this.submitButtonSelector).removeClass("primary");
            if (this.isPaymentMethodNonceReceived() || !isActive) {
                $(this.submitButtonSelector).addClass("primary");
                $(this.submitButtonSelector).html(this.reviewButtonHtml);
            }
        },

        /**
         * @override
         */
        placeOrder: function () {
            if (!this.isPaymentMethodNonceReceived()) {
                this.payWithPayPal();
            } else {
                fullScreenLoader.startLoader();

                $.when(
                    setPaymentInformationAction(
                        this.messageContainer,
                        this.getData()
                    )
                ).done(this.done.bind(this))
                    .fail(this.fail.bind(this));
            }
        },

        /**
         * {Function}
         */
        fail: function () {
            fullScreenLoader.stopLoader();

            return this;
        },

        /**
         * {Function}
         */
        done: function () {
            fullScreenLoader.stopLoader();
            $('#multishipping-billing-form').submit();

            return this;
        }
    });
});
