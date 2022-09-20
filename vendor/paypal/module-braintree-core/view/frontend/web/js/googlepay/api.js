/**
 * Braintree Google Pay button api
 **/
define([
    'uiComponent',
    'mage/translate',
    'mage/storage',
    'jquery',
    'PayPal_Braintree/js/form-builder'
], function (Component, $t, storage, jQuery, formBuilder) {
    'use strict';

    return Component.extend({
        defaults: {
            clientToken: null,
            merchantId: null,
            currencyCode: null,
            actionSuccess: null,
            amount: null,
            cardTypes: [],
            btnColor: 0
        },

        /**
         * Set & get environment
         * "PRODUCTION" or "TEST"
         */
        setEnvironment: function (value) {
            this.environment = value;
        },
        getEnvironment: function () {
            return this.environment;
        },

        /**
         * Set & get api token
         */
        setClientToken: function (value) {
            this.clientToken = value;
        },
        getClientToken: function () {
            return this.clientToken;
        },

        /**
         * Set and get display name
         */
        setMerchantId: function (value) {
            this.merchantId = value;
        },
        getMerchantId: function () {
            return this.merchantId;
        },

        /**
         * Set and get currency code
         */
        setAmount: function (value) {
            this.amount = parseFloat(value).toFixed(2);
        },
        getAmount: function () {
            return this.amount;
        },

        /**
         * Set and get currency code
         */
        setCurrencyCode: function (value) {
            this.currencyCode = value;
        },
        getCurrencyCode: function () {
            return this.currencyCode;
        },

        /**
         * Set and get success redirection url
         */
        setActionSuccess: function (value) {
            this.actionSuccess = value;
        },
        getActionSuccess: function () {
            return this.actionSuccess;
        },

        /**
         * Set and get success redirection url
         */
        setCardTypes: function (value) {
            this.cardTypes = value;
        },
        getCardTypes: function () {
            return this.cardTypes;
        },

        /**
         * BTN Color
         */
        setBtnColor: function (value) {
            this.btnColor = value;
        },
        getBtnColor: function () {
            return this.btnColor;
        },

        /**
         * Payment request info
         */
        getPaymentRequest: function () {
            var result = {
                transactionInfo: {
                    totalPriceStatus: 'ESTIMATED',
                    totalPrice: this.getAmount(),
                    currencyCode: this.getCurrencyCode()
                },
                allowedPaymentMethods: [
                    {
                        "type": "CARD",
                        "parameters": {
                            "allowedCardNetworks": this.getCardTypes(),
                            "billingAddressRequired": true,
                            "billingAddressParameters": {
                                format: 'FULL',
                                phoneNumberRequired: true
                            },
                        },

                    }
                ],
                shippingAddressRequired: true,
                emailRequired: true,
            };

            if (this.getEnvironment() !== "TEST") {
                result.merchantInfo = { merchantId: this.getMerchantId() };
            }

            return result;
        },

        /**
         * Place the order
         */
        startPlaceOrder: function (nonce, paymentData, deviceData) {
            var payload = {
                details: {
                    shippingAddress: {
                        streetAddress: paymentData.shippingAddress.address1 + "\n"
                            + paymentData.shippingAddress.address2,
                        locality: paymentData.shippingAddress.locality,
                        postalCode: paymentData.shippingAddress.postalCode,
                        countryCodeAlpha2: paymentData.shippingAddress.countryCode,
                        email: paymentData.email,
                        name: paymentData.shippingAddress.name,
                        telephone: typeof paymentData.shippingAddress.phoneNumber !== 'undefined' ? paymentData.shippingAddress.phoneNumber : '',
                        region: typeof paymentData.shippingAddress.administrativeArea !== 'undefined' ? paymentData.shippingAddress.administrativeArea : ''
                    },
                    billingAddress: {
                        streetAddress: paymentData.paymentMethodData.info.billingAddress.address1 + "\n"
                            + paymentData.paymentMethodData.info.billingAddress.address2,
                        locality: paymentData.paymentMethodData.info.billingAddress.locality,
                        postalCode: paymentData.paymentMethodData.info.billingAddress.postalCode,
                        countryCodeAlpha2: paymentData.paymentMethodData.info.billingAddress.countryCode,
                        email: paymentData.email,
                        name: paymentData.paymentMethodData.info.billingAddress.name,
                        telephone: typeof paymentData.paymentMethodData.info.billingAddress.phoneNumber !== 'undefined' ? paymentData.paymentMethodData.info.billingAddress.phoneNumber : '',
                        region: typeof paymentData.paymentMethodData.info.billingAddress.administrativeArea !== 'undefined' ? paymentData.paymentMethodData.info.billingAddress.administrativeArea : ''
                    }
                },
                nonce: nonce,
                deviceData: deviceData,
            };

            formBuilder.build({
                action: this.getActionSuccess(),
                fields: {
                    result: JSON.stringify(payload)
                }
            }).submit();
        }
    });
});
