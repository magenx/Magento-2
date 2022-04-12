define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'jquery',
        'braintree',
        'braintreeDataCollector',
        'braintreeAch',
        'PayPal_Braintree/js/form-builder',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'mage/translate'
    ],
    function (
        Component,
        ko,
        $,
        braintree,
        dataCollector,
        ach,
        formBuilder,
        messageList,
        selectBillingAddress,
        fullScreenLoader,
        quote,
        $t
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                deviceData: null,
                paymentMethodNonce: null,
                template: 'PayPal_Braintree/payment/ach',
                achInstance: null,
                routingNumber: ko.observable(""),
                accountNumber: ko.observable(""),
                accountType: ko.observable("checking"),
                ownershipType: ko.observable("personal"),
                firstName: ko.observable(""),
                lastName: ko.observable(""),
                businessName: ko.observable(""),
                hasAuthorization: ko.observable(false),
                business: ko.observable(false), // for ownership type
                personal: ko.observable(true) // for ownership type
            },

            clickAchBtn: function () {
                if (!this.validateForm('#' + this.getCode() + '-form')) {
                    return;
                }

                fullScreenLoader.startLoader();

                var self = this;

                var billingAddress = quote.billingAddress();

                let regionCode;

                let bankDetails = {
                    routingNumber: self.routingNumber(),
                    accountNumber: self.accountNumber(),
                    accountType: self.accountType(),
                    ownershipType: self.ownershipType(),
                    billingAddress: {
                        streetAddress: billingAddress.street[0],
                        extendedAddress: billingAddress.street[1],
                        locality: billingAddress.city,
                        region: billingAddress.regionCode,
                        postalCode: billingAddress.postcode,
                    }
                };

                if (bankDetails.ownershipType === 'personal') {
                    bankDetails.firstName = self.firstName();
                    bankDetails.lastName = self.lastName();
                } else {
                    bankDetails.businessName = self.businessName();
                }

                var mandateText = document.getElementById('braintree-ach-mandate').textContent;

                // if no region code is available, lets find one!
                if (typeof billingAddress.regionCode === 'undefined') {
                    $.get('/rest/V1/directory/countries/' + billingAddress.countryId).done(function (data) {
                        if (typeof data.available_regions !== 'undefined') {
                            for (var i = 0; i < data.available_regions.length; ++i) {
                                if (data.available_regions[i].id === billingAddress.regionId) {
                                    regionCode = data.available_regions[i].code;
                                    bankDetails.billingAddress.region = regionCode;
                                    self.tokenizeAch(bankDetails, mandateText);
                                }
                            }
                        }
                    }).fail(function() {
                        fullScreenLoader.stopLoader();
                    });
                } else {
                    self.tokenizeAch(bankDetails, mandateText);
                }
            },

            tokenizeAch: function (bankDetails, mandateText) {
                var self = this;
                this.achInstance.tokenize({
                    bankDetails: bankDetails,
                    mandateText: mandateText
                }, function (tokenizeErr, tokenizedPayload) {
                    if (tokenizeErr) {
                        self.setErrorMsg($t('There was an error with the provided bank details. Please check and try again.'));
                        self.hasAuthorization(false);
                    } else {
                        fullScreenLoader.stopLoader();
                        self.handleAchSuccess(tokenizedPayload);
                    }
                });
            },

            getClientToken: function () {
                return window.checkoutConfig.payment[this.getCode()].clientToken;
            },

            getCode: function () {
                return 'braintree_ach_direct_debit';
            },

            getStoreName: function () {
                return window.checkoutConfig.payment[this.getCode()].storeName;
            },

            getData: function () {
                let data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'payment_method_nonce': this.paymentMethodNonce,
                    }
                };

                data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

                return data;
            },

            getTitle: function() {
                return 'ACH Direct Debit';
            },

            handleAchSuccess: function (payload) {
                this.setPaymentMethodNonce(payload.nonce);
                this.placeOrder();
            },

            initialize: function () {
                this._super();

                var self = this;

                braintree.create({
                    authorization: self.getClientToken()
                }, function (clientError, clientInstance) {
                    if (clientError) {
                        this.setErrorMsg($t('Unable to initialize Braintree Client.'));
                        return;
                    }

                    ach.create({
                        client: clientInstance
                    }, function (achErr, achInstance) {
                        if (achErr) {
                            self.setErrorMsg($t('Error initializing ACH: %1').replace('%1', achErr));
                            return;
                        }

                        self.setAchInstance(achInstance);
                    });
                });

                return this;
            },

            isAllowed: function () {
                return window.checkoutConfig.payment[this.getCode()].isAllowed;
            },

            changeOwnershipType: function (data, event) {
                var self = this;
                if (event.currentTarget.value === 'business') {
                    self.business(true);
                    self.personal(false);
                } else {
                    self.business(false);
                    self.personal(true);
                }
            },

            isBusiness: function () {
                return this.business;
            },

            isPersonal: function () {
                return this.personal;
            },

            setErrorMsg: function (message) {
                messageList.addErrorMessage({
                    message: message
                });
            },

            setPaymentMethodNonce: function (nonce) {
                this.paymentMethodNonce = nonce;
            },

            setAchInstance: function (instance) {
                this.achInstance = instance;
            },

            validateForm: function (form) {
                return $(form).validation() && $(form).validation('isValid');
            }
        });
    }
);
