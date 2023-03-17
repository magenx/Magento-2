/**
 * Copyright 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define([
    'jquery',
    'PayPal_Braintree/js/view/payment/method-renderer/cc-form',
    'PayPal_Braintree/js/validator',
    'Magento_Vault/js/view/payment/vault-enabler',
    'Magento_Checkout/js/model/payment/additional-validators',
    'mage/translate'
], function ($, Component, validator, VaultEnabler, additionalValidators, $t) {
    'use strict';

    return Component.extend({

        defaults: {
            template: 'PayPal_Braintree/payment/form',
            clientConfig: {

                /**
                 * {String}
                 */
                id: 'co-transparent-form-braintree'
            },
            isValidCardNumber: false,
            isValidExpirationDate: false,
            isValidCvvNumber: false,

            onInstanceReady: function (instance) {
                instance.on('validityChange', this.onValidityChange.bind(this));
                instance.on('cardTypeChange', this.onCardTypeChange.bind(this));
            }
        },

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();
            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());

            return this;
        },

        /**
         * Init config
         */
        initClientConfig: function () {
            this._super();

            this.clientConfig.hostedFields = this.getHostedFields();
            this.clientConfig.onInstanceReady = this.onInstanceReady.bind(this);
        },

        /**
         * @returns {Object}
         */
        getData: function () {
            var data = this._super();

            this.vaultEnabler.visitAdditionalData(data);

            return data;
        },

        /**
         * @returns {Bool}
         */
        isVaultEnabled: function () {
            return this.vaultEnabler.isVaultEnabled();
        },

        /**
         * Get Braintree Hosted Fields
         * @returns {Object}
         */
        getHostedFields: function () {
            var self = this,
                fields = {
                    number: {
                        selector: self.getSelector('cc_number'),
                        placeholder: $t('4111 1111 1111 1111')
                    },
                    expirationDate: {
                        selector: self.getSelector('expirationDate'),
                        placeholder: $t('MM/YYYY')
                    }
                };

            if (self.hasVerification()) {
                fields.cvv = {
                    selector: self.getSelector('cc_cid'),
                    placeholder: $t('123')
                };
            }

            return fields;
        },

        /**
         * Triggers on Hosted Field changes
         * @param {Object} event
         * @returns {Boolean}
         */
        onValidityChange: function (event) {
            // Handle a change in validation or card type
            if (event.emittedBy === 'number') {
                this.selectedCardType(null);

                if (event.cards.length === 1) {
                    this.isValidCardNumber = event.fields.number.isValid;
                    this.selectedCardType(
                        validator.getMageCardType(event.cards[0].type, this.getCcAvailableTypes())
                    );
                    this.validateCardType();
                } else {
                    this.isValidCardNumber = event.fields.number.isValid;
                    this.validateCardType();
                }
            }

            // Other field validations
            if (event.emittedBy === 'expirationDate') {
                this.isValidExpirationDate = event.fields.expirationDate.isValid;
            }
            if (event.emittedBy === 'cvv') {
                this.isValidCvvNumber = event.fields.cvv.isValid;
            }
        },

        /**
         * Triggers on Hosted Field card type changes
         * @param {Object} event
         * @returns {Boolean}
         */
        onCardTypeChange: function (event) {
            if (event.cards.length === 1) {
                this.selectedCardType(
                    validator.getMageCardType(event.cards[0].type, this.getCcAvailableTypes())
                );
            } else {
                this.selectedCardType(null);
            }
        },

        /**
         * Toggle invalid class on selector
         * @param selector
         * @param state
         * @returns {boolean}
         */
        validateField: function (selector, state) {
            var $selector = $(this.getSelector(selector)),
                invalidClass = 'braintree-hosted-fields-invalid';

            if (state === true) {
                $selector.removeClass(invalidClass);
                return true;
            }

            $selector.addClass(invalidClass);
            return false;
        },

        /**
         * Validate current credit card type
         * @returns {Boolean}
         */
        validateCardType: function () {
            return this.validateField(
                'cc_number',
                (this.isValidCardNumber)
            );
        },

        /**
         * Validate current expiry date
         * @returns {boolean}
         */
        validateExpirationDate: function () {
            return this.validateField(
                'expirationDate',
                (this.isValidExpirationDate === true)
            );
        },

        /**
         * Validate current CVV field
         * @returns {boolean}
         */
        validateCvvNumber: function () {
            var self = this;

            if (self.hasVerification() === false) {
                return true;
            }

            return this.validateField(
                'cc_cid',
                (this.isValidCvvNumber === true)
            );
        },

        /**
         * Validate all fields
         * @returns {boolean}
         */
        validateFormFields: function () {
            return (this.validateCardType() && this.validateExpirationDate() && this.validateCvvNumber()) === true;
        },

        /**
         * Trigger order placing
         */
        placeOrderClick: function () {
            if (this.validateFormFields() && additionalValidators.validate()) {
                this.placeOrder();
            }
        },
        /**
         * @returns {String}
         */
        getVaultCode: function () {
            return window.checkoutConfig.payment[this.getCode()].ccVaultCode;
        }
    });
});
