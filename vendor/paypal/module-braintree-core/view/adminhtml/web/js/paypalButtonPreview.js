/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery',
    'braintree',
    'braintreePayPalCheckout',
    'domReady!'
], function (_, $, braintree, paypalCheckout) {
    'use strict';
    let buttonIds = [];

    return {
        events: {
            onClick: null
        },

        /**
         * @param token
         * @param currency
         * @param env
         * @param local
         */
        init: function (token, currency, env, local) {
            buttonIds = [];
            $('.action-braintree-paypal-logo').each(function () {
                if (!$(this).hasClass("button-loaded")) {
                    $(this).addClass('button-loaded');
                    buttonIds.push($(this).attr('id'));
                }
            });

            if (buttonIds.length > 0) {
                this.loadSDK(token, currency, env, local);
            }
        },

        /**
         * Load Braintree PayPal SDK
         * @param token
         * @param currency
         * @param env
         * @param local
         */
        loadSDK: function (token, currency, env, local) {
            braintree.create({
                authorization: token
            }, function (clientErr, clientInstance) {
                if (clientErr) {
                    console.error('paypalCheckout error', clientErr);
                    return this.showError("PayPal Checkout could not be initialized. Please contact the store owner.");
                }
                paypalCheckout.create({
                    client: clientInstance
                }, function (err, paypalCheckoutInstance) {
                    if (typeof paypal !== 'undefined' ) {
                        this.renderPayPalButtons(buttonIds);
                        this.renderPayPalMessages();
                    } else {
                        var configSDK = {
                            components: 'buttons,messages,funding-eligibility',
                            "enable-funding": "paylater",
                            currency: currency
                        };
                        if (env === 'sandbox' && (local !== '' || local !== 'undefined')) {
                            configSDK["buyer-country"] = local;
                        }
                        paypalCheckoutInstance.loadPayPalSDK(configSDK, function () {
                            this.renderPayPalButtons(buttonIds);
                            this.renderPayPalMessages();
                        }.bind(this));
                    }
                }.bind(this));
            }.bind(this));
        },

        /**
         * Render PayPal buttons
         * @param ids
         */
        renderPayPalButtons: function (ids) {
            _.each(ids, function (id) {
                this.payPalButton(id);
            }.bind(this));
        },

        /**
         * Render PayPal messages
         */
        renderPayPalMessages: function () {
            $('.action-braintree-paypal-message').each(function () {
                let messages = paypal.Messages({
                    amount: $(this).data('pp-amount'),
                    pageType: $(this).data('pp-type'),
                    style: {
                        layout: $(this).data('messaging-layout'),
                        text: {
                            color:   $(this).data('messaging-text-color')
                        },
                        logo: {
                            type: $(this).data('messaging-logo'),
                            position: $(this).data('messaging-logo-position')
                        }
                    }
                });

                if ($('#' + $(this).attr('id')).length && $(this).data('messaging-show')) {
                    messages.render('#' + $(this).attr('id'));
                }
            });
        },

        /**
         * @param id
         */
        payPalButton: function (id) {
            let data = $('#' + id);
            let style = {
                color: data.data('color'),
                shape: data.data('shape'),
                size: data.data('size'),
                label: data.data('label')
            };

            if (data.data('fundingicons')) {
                style.fundingicons = data.data('fundingicons');
            }

            // Render
            var button = paypal.Buttons({
                fundingSource: data.data('funding'),
                style: style,

                onInit: function (data, actions) {
                    actions.disable();
                }
            });
            if (!button.isEligible()) {
                console.log('PayPal button is not elligible');
                data.parent().remove();
                return;
            }
            if ($('#' + data.attr('id')).length && data.data('show')) {
                button.render('#' + data.attr('id'));
            }
        },
    }
});
