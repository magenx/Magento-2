/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'mage/translate',
    'PayPal_Braintree/js/validator',
    'braintree',
    'braintreeHostedFields'
], function ($, Class, alert, domObserver, $t, validator, client, hostedFields) {
    'use strict';

    return Class.extend({

        defaults: {
            $selector: null,
            selector: 'edit_form',
            container: 'payment_form_braintree',
            active: false,
            scriptLoaded: false,
            braintree: null,
            selectedCardType: null,
            imports: {
                onActiveChange: 'active'
            },
            hostedFieldsInstance: null
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            var self = this;

            validator.setConfig(this);

            self.$selector = $('#' + self.selector);
            this._super()
                .observe([
                    'active',
                    'scriptLoaded',
                    'selectedCardType'
                ]);

            // re-init payment method events
            self.$selector.off('changePaymentMethod.' + this.code)
                .on('changePaymentMethod.' + this.code, this.changePaymentMethod.bind(this));

            // listen block changes
            domObserver.get('#' + self.container, function () {
                if (self.scriptLoaded()) {
                    self.$selector.off('submit');
                    self.initBraintree();
                }
            });

            return this;
        },

        /**
         * Enable/disable current payment method
         * @param {Object} event
         * @param {String} method
         * @returns {exports.changePaymentMethod}
         */
        changePaymentMethod: function (event, method) {
            this.active(method === this.code);
            return this;
        },

        /**
         * Triggered when payment changed
         * @param {Boolean} isActive
         */
        onActiveChange: function (isActive) {
            if (!isActive) {
                this.$selector.off('submitOrder.braintree');
                this.$selector.on('submitOrder', function () {
                    $('#payment_form_braintree').find('[type="submit"]').trigger('click');
                    $('#edit_form').trigger('realOrder');
                });

                return;
            }
            this.disableEventListeners();

            if (typeof window.order !== 'undefined') {
                window.order.addExcludedPaymentMethod(this.code);
            }

            if (!this.clientToken) {
                this.error($.mage.__('This payment is not available'));

                return;
            }

            this.enableEventListeners();

            if (!this.scriptLoaded()) {
                this.initBraintree();
            }
        },

        /**
         * Setup Braintree SDK
         */
        initBraintree: function () {
            var self = this;
            this.scriptLoaded(true);

            self.disableEventListeners();

            try {
                $('body').trigger('processStart');

                client.create({
                    authorization: self.clientToken
                }, function (clientErr, clientInstance) {
                    if (clientErr) {
                        console.error('Error!', clientErr);
                        return self.error(response.clientErr);
                    }

                    hostedFields.create({
                        client: clientInstance,
                        fields: self.getHostedFields()
                    }, function (createErr, hostedFieldsInstance) {
                        if (createErr) {
                            self.error($t(createErr));
                            console.error('Error!', createErr);
                            return;
                        }

                        self.hostedFieldsInstance = hostedFieldsInstance;
                        self.enableEventListeners();

                        $('body').trigger('processStop');
                    }.bind(this));
                }.bind(this));
            } catch (e) {
                $('body').trigger('processStop');
                self.error(e.message);
                console.log(e);
            }
        },

        /**
         * Get hosted fields configuration
         * @returns {Object}
         */
        getHostedFields: function () {
            var self = this,
                fields = {
                    number: {
                        selector: self.getSelector('cc_number'),
                        placeholder: $t('4111 1111 1111 1111')
                    },
                    expirationMonth: {
                        selector: self.getSelector('cc_exp_month'),
                        placeholder: $t('MM')
                    },
                    expirationYear: {
                        selector: self.getSelector('cc_exp_year'),
                        placeholder: $t('YY')
                    }
                };

            if (self.useCvv) {
                fields.cvv = {
                    selector: self.getSelector('cc_cid'),
                    placeholder: $t('123')
                };
            }

            return fields;
        },

        /**
         * Show alert message
         * @param {String} message
         */
        error: function (message) {
            alert({
                content: message
            });
        },

        /**
         * Enable form event listeners
         */
        enableEventListeners: function () {
            this.$selector.on('submitOrder.braintree', this.submitOrder.bind(this));
        },

        /**
         * Disable form event listeners
         */
        disableEventListeners: function () {
            this.$selector.off('submitOrder');
            this.$selector.off('submit');
        },

        /**
         * Store payment details
         * @param {String} nonce
         */
        setPaymentDetails: function (nonce) {
            var $container = $('#' + this.container);

            $container.find('[name="payment[payment_method_nonce]"]').val(nonce);
        },

        /**
         * Trigger order submit
         */
        submitOrder: function () {
            this.$selector.validate().form();
            this.$selector.trigger('afterValidate.beforeSubmit');
            $('body').trigger('processStop');

            // validate parent form
            if (this.$selector.validate().errorList.length) {
                return false;
            }

            $('body').trigger('processStart');
            this.tokenizeHostedFields();
        },

        /**
         * Place order
         */
        placeOrder: function () {
            $('#' + this.selector).trigger('realOrder');
        },

        /**
         * Get list of currently available card types
         * @returns {Array}
         */
        getCcAvailableTypes: function () {
            var types = [],
                $options = $(this.getSelector('cc_type')).find('option');

            $.map($options, function (option) {
                types.push($(option).val());
            });

            return types;
        },

        /**
         * Get jQuery selector
         * @param {String} field
         * @returns {String}
         */
        getSelector: function (field) {
            return '#' + this.code + '_' + field;
        },

        tokenizeHostedFields: function () {
            this.hostedFieldsInstance.tokenize({
                vault: false // vault or no?
            }, function (tokenizeErr, payload) {
                if (tokenizeErr) {
                    $('body').trigger('processStop');
                    switch (tokenizeErr.code) {
                        case 'HOSTED_FIELDS_FIELDS_EMPTY':
                            // occurs when none of the fields are filled in
                            this.error($t('Please enter a card number, expiration date and CVV'));
                            break;
                        case 'HOSTED_FIELDS_FIELDS_INVALID':
                            // occurs when certain fields do not pass client side validation
                            this.error($t('Please correct the problems with the Credit Card fields.'));
                            console.error('Some fields are invalid:', tokenizeErr.details.invalidFieldKeys);
                            break;
                        case 'HOSTED_FIELDS_TOKENIZATION_FAIL_ON_DUPLICATE':
                            // occurs when:
                            //   * the client token used for client authorization was generated
                            //     with a customer ID and the fail on duplicate payment method
                            //     option is set to true
                            //   * the card being tokenized has previously been vaulted (with any customer)
                            // See: https://developers.braintreepayments.com/reference/request/client-token/generate/#options.fail_on_duplicate_payment_method
                            this.error($t('The payment method used, already exists in the user\'s vault. Please use the vault option instead.'));
                            break;
                        case 'HOSTED_FIELDS_TOKENIZATION_CVV_VERIFICATION_FAILED':
                            // occurs when:
                            //   * the client token used for client authorization was generated
                            //     with a customer ID and the verify card option is set to true
                            //     and you have credit card verification turned on in the Braintree
                            //     control panel
                            //   * the cvv does not pass verfication (https://developers.braintreepayments.com/reference/general/testing/#avs-and-cvv/cid-responses)
                            // See: https://developers.braintreepayments.com/reference/request/client-token/generate/#options.verify_card
                            this.error($t('CVV did not pass verification'));
                            break;
                        case 'HOSTED_FIELDS_FAILED_TOKENIZATION':
                            // occurs for any other tokenization error on the server
                            this.error($t('There was an issue tokenizing the card. Please check the card is valid.'));
                            console.error('Tokenization failed server side. Is the card valid?');
                            break;
                        case 'HOSTED_FIELDS_TOKENIZATION_NETWORK_ERROR':
                            // occurs when the Braintree gateway cannot be contacted
                            this.error($t('There was an error connecting to Braintree. Please try again.'));
                            break;
                        default:
                            this.error($t('There was an issue processing the payment. Please try again.'));
                            console.error('Braintree error', tokenizeErr);
                            break;
                    }
                } else {
                    this.setPaymentDetails(payload.nonce);
                    $('#' + this.container).find('[type="submit"]').trigger('click');
                }
            }.bind(this));
        }
    });
});
