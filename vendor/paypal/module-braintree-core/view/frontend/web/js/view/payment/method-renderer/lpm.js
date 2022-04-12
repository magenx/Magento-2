define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'jquery',
        'braintree',
        'braintreeLpm',
        'PayPal_Braintree/js/form-builder',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'mage/translate'
    ],
    function (
        Component,
        ko,
        $,
        braintree,
        lpm,
        formBuilder,
        messageList,
        selectBillingAddress,
        fullScreenLoader,
        quote,
        additionalValidators,
        url,
        $t
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                code: 'braintree_local_payment',
                paymentMethodsAvailable: ko.observable(false),
                paymentMethodNonce: null,
                template: 'PayPal_Braintree/payment/lpm'
            },

            clickPaymentBtn: function (method) {
                var self = this;

                if (additionalValidators.validate()) {
                    fullScreenLoader.startLoader();

                    braintree.create({
                        authorization: self.getClientToken()
                    }, function (clientError, clientInstance) {
                        if (clientError) {
                            self.setErrorMsg($t('Unable to initialize Braintree Client.'));
                            fullScreenLoader.stopLoader();
                            return;
                        }

                        lpm.create({
                            client: clientInstance,
                            merchantAccountId: self.getMerchantAccountId()
                        }, function (lpmError, lpmInstance) {
                            if (lpmError) {
                                self.setErrorMsg(lpmError);
                                fullScreenLoader.stopLoader();
                                return;
                            }

                            lpmInstance.startPayment({
                                amount: self.getAmount(),
                                currencyCode: self.getCurrencyCode(),
                                email: self.getCustomerDetails().email,
                                phone: self.getCustomerDetails().phone,
                                givenName: self.getCustomerDetails().firstName,
                                surname: self.getCustomerDetails().lastName,
                                shippingAddressRequired: !quote.isVirtual(),
                                address: self.getAddress(),
                                paymentType: method,
                                onPaymentStart: function (data, start) {
                                    start();
                                },
                                // This is a required option, however it will apparently never be used in the current payment flow.
                                // Therefore, both values are set to allow the payment flow to continute, rather than erroring out.
                                fallback: {
                                    url: 'N/A',
                                    buttonText: 'N/A'
                                }
                            }, function (startPaymentError, payload) {
                                fullScreenLoader.stopLoader();
                                if (startPaymentError) {
                                    switch (startPaymentError.code) {
                                        case 'LOCAL_PAYMENT_POPUP_CLOSED':
                                            self.setErrorMsg($t('Local Payment popup was closed unexpectedly.'));
                                            break;
                                        case 'LOCAL_PAYMENT_WINDOW_OPEN_FAILED':
                                            self.setErrorMsg($t('Local Payment popup failed to open.'));
                                            break;
                                        case 'LOCAL_PAYMENT_WINDOW_CLOSED':
                                            self.setErrorMsg($t('Local Payment popup was closed. Payment cancelled.'));
                                            break;
                                        default:
                                            self.setErrorMsg('Error! ' + startPaymentError);
                                            break;
                                    }
                                } else {
                                    // Send the nonce to your server to create a transaction
                                    self.setPaymentMethodNonce(payload.nonce);
                                    self.placeOrder();
                                }
                            });
                        });
                    });
                }
            },

            getAddress: function () {
                var shippingAddress = quote.shippingAddress();

                if (quote.isVirtual()) {
                    return {
                        countryCode: shippingAddress.countryId
                    }
                }

                return {
                    streetAddress: shippingAddress.street[0],
                    extendedAddress: shippingAddress.street[1],
                    locality: shippingAddress.city,
                    postalCode: shippingAddress.postcode,
                    region: shippingAddress.region,
                    countryCode: shippingAddress.countryId
                }
            },

            getAmount: function () {
                return quote.totals()['base_grand_total'].toString();
            },

            getBillingAddress: function () {
                return quote.billingAddress();
            },

            getClientToken: function () {
                return window.checkoutConfig.payment[this.getCode()].clientToken;
            },

            getCode: function () {
                return this.code;
            },

            getCurrencyCode: function () {
                return quote.totals()['base_currency_code'];
            },

            getCustomerDetails: function () {
                var billingAddress = quote.billingAddress();
                return {
                    firstName: billingAddress.firstname,
                    lastName: billingAddress.lastname,
                    phone: billingAddress.telephone,
                    email: typeof quote.guestEmail === 'string' ? quote.guestEmail : window.checkoutConfig.customerData.email
                }
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

            getMerchantAccountId: function () {
                return window.checkoutConfig.payment[this.getCode()].merchantAccountId;
            },

            getPaymentMethod: function (method) {
                var methods = this.getPaymentMethods();

                for (var i = 0; i < methods.length; i++) {
                    if (methods[i].method === method) {
                        return methods[i]
                    }
                }
            },

            getPaymentMethods: function () {
                return window.checkoutConfig.payment[this.getCode()].allowedMethods;
            },

            getPaymentMarkSrc: function () {
                return window.checkoutConfig.payment[this.getCode()].paymentIcons;
            },

            getTitle: function () {
                return window.checkoutConfig.payment[this.getCode()].title;
            },

            initialize: function () {
                this._super();
                return this;
            },

            isActive: function () {
                var address = quote.billingAddress() || quote.shippingAddress();
                var methods = this.getPaymentMethods();

                for (var i = 0; i < methods.length; i++) {
                    if (methods[i].countries.includes(address.countryId)) {
                        return true;
                    }
                }

                return false;
            },

            isValidCountryAndCurrency: function (method) {
                var address = quote.billingAddress();

                if (!address) {
                    this.paymentMethodsAvailable(false);
                    return false;
                }

                var countryId = address.countryId;
                var quoteCurrency = quote.totals()['base_currency_code'];
                var paymentMethodDetails = this.getPaymentMethod(method);

                if ((countryId !== 'GB' && paymentMethodDetails.countries.includes(countryId) && (quoteCurrency === 'EUR' || quoteCurrency === 'PLN')) || (countryId === 'GB' && paymentMethodDetails.countries.includes(countryId) && quoteCurrency === 'GBP')) {
                    this.paymentMethodsAvailable(true);
                    return true;
                }

                return false;
            },

            setErrorMsg: function (message) {
                messageList.addErrorMessage({
                    message: message
                });
            },

            setPaymentMethodNonce: function (nonce) {
                this.paymentMethodNonce = nonce;
            },

            validateForm: function (form) {
                return $(form).validation() && $(form).validation('isValid');
            }
        });
    }
);
