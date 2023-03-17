/**
 * Copyright 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'braintree',
    'braintreeDataCollector',
    'braintreeHostedFields',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Ui/js/model/messageList',
    'mage/translate'
], function ($, client, dataCollector, hostedFields, fullScreenLoader, globalMessageList, $t) {
    'use strict';

    return {
        apiClient: null,
        config: {},
        checkout: null,
        deviceData: null,
        clientInstance: null,
        hostedFieldsInstance: null,
        paypalInstance: null,
        code: 'braintree',

        /**
         * {Object}
         */
        events: {
            onClick: null,
            onCancel: null,
            onError: null
        },

        /**
         * Get Braintree api client
         * @returns {Object}
         */
        getApiClient: function () {
            return this.clientInstance;
        },

        /**
         * Set configuration
         * @param {Object} config
         */
        setConfig: function (config) {
            this.config = config;
        },

        /**
         * Get payment name
         * @returns {String}
         */
        getCode: function () {
            if (window.checkoutConfig.payment[this.code]) {
                return this.code;
            } else {
                return 'braintree_paypal';
            }
        },

        /**
         * Get client token
         * @returns {String|*}
         */
        getClientToken: function () {
            return window.checkoutConfig.payment[this.getCode()].clientToken;
        },

        /**
         * @returns {String}
         */
        getEnvironment: function () {
            return window.checkoutConfig.payment[this.getCode()].environment;
        },

        getCurrentCode: function (paypalType = null) {
            var code = 'braintree_paypal';
            if (paypalType !== 'paypal') {
                code = code + '_' + paypalType;
            }
            return code;
        },

        /**
         * @returns {String}
         */
        getColor: function (paypalType = null) {
            return window.checkoutConfig.payment[this.getCurrentCode(paypalType)].style.color;
        },

        /**
         * @returns {String}
         */
        getShape: function (paypalType = null) {
            return window.checkoutConfig.payment[this.getCurrentCode(paypalType)].style.shape;
        },

        /**
         * @returns {String}
         */
        getSize: function (paypalType = null) {
            return window.checkoutConfig.payment[this.getCurrentCode(paypalType)].style.size;
        },

        /**
         * @returns {String}
         */
        getLabel: function (paypalType = null) {
            return window.checkoutConfig.payment[this.getCurrentCode(paypalType)].style.label;
        },

        /**
         * @returns {String}
         */
        getBranding: function () {
            return null;
        },

        /**
         * @returns {String}
         */
        getFundingIcons: function () {
            return null;
        },

        /**
         * @returns {String}
         */
        getDisabledFunding: function () {
            return window.checkoutConfig.payment[this.getCode()].disabledFunding;
        },

        /**
         * Show error message
         *
         * @param {String} errorMessage
         */
        showError: function (errorMessage) {
            globalMessageList.addErrorMessage({
                message: errorMessage
            });
            fullScreenLoader.stopLoader(true);
        },

        /**
         * Disable submit button
         */
        disableButton: function () {
            // stop any previous shown loaders
            fullScreenLoader.stopLoader(true);
            fullScreenLoader.startLoader();
            $('[data-button="place"]').attr('disabled', 'disabled');
        },

        /**
         * Enable submit button
         */
        enableButton: function () {
            $('[data-button="place"]').removeAttr('disabled');
            fullScreenLoader.stopLoader();
        },

        /**
         * Has PayPal been init'd already
         */
        getPayPalInstance: function() {
            if (typeof this.config.paypalInstance !== 'undefined' && this.config.paypalInstance) {
                return this.config.paypalInstance;
            }

            return null;
        },

        setPayPalInstance: function(val) {
            this.config.paypalInstance = val;
        },

        /**
         * Setup Braintree SDK
         */
        setup: function (callback) {
            if (!this.getClientToken()) {
                this.showError($t('Sorry, but something went wrong.'));
                return;
            }

            if (this.clientInstance) {
                if (typeof this.config.onReady === 'function') {
                    this.config.onReady(this);
                }

                if (typeof callback === "function") {
                    callback(this.clientInstance);
                }
                return;
            }

            client.create({
                authorization: this.getClientToken()
            }, function (clientErr, clientInstance) {
                if (clientErr) {
                    console.error('Braintree Setup Error', clientErr);
                    return this.showError("Sorry, but something went wrong. Please contact the store owner.");
                }

                var options = {
                    client: clientInstance
                };

                if (typeof this.config.dataCollector === 'object' && typeof this.config.dataCollector.paypal === 'boolean') {
                    options.paypal = true;
                }

                dataCollector.create(options, function (err, dataCollectorInstance) {
                    if (err) {
                        return console.log(err);
                    }

                    this.deviceData = dataCollectorInstance.deviceData;
                    this.config.onDeviceDataRecieved(this.deviceData);
                }.bind(this));

                this.clientInstance = clientInstance;

                if (typeof this.config.onReady === 'function') {
                    this.config.onReady(this);
                }

                if (typeof callback === "function") {
                    callback(this.clientInstance);
                }
            }.bind(this));
        },

        /**
         * Setup hosted fields instance
         */
        setupHostedFields: function () {
            var self = this;

            if (this.hostedFieldsInstance) {
                this.hostedFieldsInstance.teardown(function () {
                    this.hostedFieldsInstance = null;
                    this.setupHostedFields();
                }.bind(this));
                return;
            }

            hostedFields.create({
                client: this.clientInstance,
                fields: this.config.hostedFields,
                styles: {
                    "input": {
                        "font-size": "14pt",
                        "color": "#3A3A3A"
                    },
                    ":focus": {
                        "color": "black"
                    },
                    ".valid": {
                        "color": "green"
                    },
                    ".invalid": {
                        "color": "red"
                    }
                }
            }, function (createErr, hostedFieldsInstance) {
                if (createErr) {
                    self.showError($t("Braintree hosted fields could not be initialized. Please contact the store owner."));
                    console.error('Braintree hosted fields error', createErr);
                    return;
                }

                this.config.onInstanceReady(hostedFieldsInstance);
                this.hostedFieldsInstance = hostedFieldsInstance;
            }.bind(this));
        },

        tokenizeHostedFields: function () {
            this.hostedFieldsInstance.tokenize({}, function (tokenizeErr, payload) {
                if (tokenizeErr) {
                    switch (tokenizeErr.code) {
                        case 'HOSTED_FIELDS_FIELDS_EMPTY':
                            // occurs when none of the fields are filled in
                            console.error('All fields are empty! Please fill out the form.');
                            break;
                        case 'HOSTED_FIELDS_FIELDS_INVALID':
                            // occurs when certain fields do not pass client side validation
                            console.error('Some fields are invalid:', tokenizeErr.details.invalidFieldKeys);
                            break;
                        case 'HOSTED_FIELDS_TOKENIZATION_FAIL_ON_DUPLICATE':
                            // occurs when:
                            //   * the client token used for client authorization was generated
                            //     with a customer ID and the fail on duplicate payment method
                            //     option is set to true
                            //   * the card being tokenized has previously been vaulted (with any customer)
                            // See: https://developers.braintreepayments.com/reference/request/client-token/generate/#options.fail_on_duplicate_payment_method
                            console.error('This payment method already exists in your vault.');
                            break;
                        case 'HOSTED_FIELDS_TOKENIZATION_CVV_VERIFICATION_FAILED':
                            // occurs when:
                            //   * the client token used for client authorization was generated
                            //     with a customer ID and the verify card option is set to true
                            //     and you have credit card verification turned on in the Braintree
                            //     control panel
                            //   * the cvv does not pass verfication (https://developers.braintreepayments.com/reference/general/testing/#avs-and-cvv/cid-responses)
                            // See: https://developers.braintreepayments.com/reference/request/client-token/generate/#options.verify_card
                            console.error('CVV did not pass verification');
                            break;
                        case 'HOSTED_FIELDS_FAILED_TOKENIZATION':
                            // occurs for any other tokenization error on the server
                            console.error('Tokenization failed server side. Is the card valid?');
                            break;
                        case 'HOSTED_FIELDS_TOKENIZATION_NETWORK_ERROR':
                            // occurs when the Braintree gateway cannot be contacted
                            console.error('Network error occurred when tokenizing.');
                            break;
                        default:
                            console.error('Something bad happened!', tokenizeErr);
                    }
                } else {
                    this.config.onPaymentMethodReceived(payload);
                }
            }.bind(this));
        }
    };
});

