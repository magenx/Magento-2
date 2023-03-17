/**
 * Braintree Apple Pay payment method integration.
 **/
define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'PayPal_Braintree/js/applepay/button'
], function (
    Component,
    quote,
    button
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PayPal_Braintree/applepay/core-checkout',
            paymentMethodNonce: null,
            deviceData: null,
            grandTotalAmount: 0,
            deviceSupported: button.deviceSupported()
        },

        /**
         * Inject the apple pay button into the target element
         */
        getApplePayBtn: function (id) {
            button.init(
                document.getElementById(id),
                this
            );
        },

        /**
         * Subscribe to grand totals
         */
        initObservable: function () {
            this._super();
            this.grandTotalAmount = parseFloat(quote.totals()['base_grand_total']).toFixed(2);

            quote.totals.subscribe(function () {
                if (this.grandTotalAmount !== quote.totals()['base_grand_total']) {
                    this.grandTotalAmount = parseFloat(quote.totals()['base_grand_total']).toFixed(2);
                }
            }.bind(this));

            return this;
        },

        /**
         * Apple pay place order method
         */
        startPlaceOrder: function (nonce, event, session, device_data) {
            this.setPaymentMethodNonce(nonce);
            this.setDeviceData(device_data);
            this.placeOrder();

            session.completePayment(ApplePaySession.STATUS_SUCCESS);
        },

        /**
         * Save nonce
         */
        setPaymentMethodNonce: function (nonce) {
            this.paymentMethodNonce = nonce;
        },

        /**
         * Save nonce
         */
        setDeviceData: function (device_data) {
            this.deviceData = device_data;
        },

        /**
         * Retrieve the client token
         * @returns null|string
         */
        getClientToken: function () {
            return window.checkoutConfig.payment[this.getCode()].clientToken;
        },

        /**
         * Payment request data
         */
        getPaymentRequest: function () {
            return {
                total: {
                    label: this.getDisplayName(),
                    amount: this.grandTotalAmount
                }
            };
        },

        /**
         * Merchant display name
         */
        getDisplayName: function () {
            return window.checkoutConfig.payment[this.getCode()].merchantName;
        },

        /**
         * Get data
         * @returns {Object}
         */
        getData: function () {
            var data = {
                'method': this.getCode(),
                'additional_data': {
                    'payment_method_nonce': this.paymentMethodNonce,
                    'device_data': this.deviceData
                }
            };
            return data;
        },

        /**
         * Return image url for the apple pay mark
         */
        getPaymentMarkSrc: function () {
            return window.checkoutConfig.payment[this.getCode()].paymentMarkSrc;
        }
    });
});
