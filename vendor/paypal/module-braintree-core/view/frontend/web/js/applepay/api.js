/**
 * Braintree Apple Pay button API
 *
 **/
define(
    [
        'jquery',
        'underscore',
        'uiComponent',
        'mage/translate',
        'mage/storage',
        'Magento_Customer/js/customer-data'
    ],
    function (
        $,
        _,
        Component,
        $t,
        storage,
        customerData
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                clientToken: null,
                quoteId: 0,
                displayName: null,
                actionSuccess: null,
                grandTotalAmount: 0,
                isLoggedIn: false,
                storeCode: "default",
                shippingAddress: {},
                countryDirectory: null,
                shippingMethods: {}
            },

            initialize: function () {
                this._super();
                if (!this.countryDirectory) {
                    storage.get("rest/V1/directory/countries").done(function (result) {
                        this.countryDirectory = {};
                        let i, data, x, region;
                        for (i = 0; i < result.length; ++i) {
                            data = result[i];
                            this.countryDirectory[data.two_letter_abbreviation] = {};
                            if (typeof data.available_regions !== 'undefined') {
                                for (x = 0; x < data.available_regions.length; ++x) {
                                    region = data.available_regions[x];
                                    this.countryDirectory[data.two_letter_abbreviation][region.name.toLowerCase().replace(/[^A-Z0-9]/ig, '')] = region.id;
                                }
                            }
                        }
                    }.bind(this));
                }
            },

            /**
             * Get region ID
             */
            getRegionId: function (countryCode, regionName) {
                if (typeof regionName !== 'string') {
                    return null;
                }

                regionName = regionName.toLowerCase().replace(/[^A-Z0-9]/ig, '');

                if (typeof this.countryDirectory[countryCode] !== 'undefined' && typeof this.countryDirectory[countryCode][regionName] !== 'undefined') {
                    return this.countryDirectory[countryCode][regionName];
                }

                return 0;
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
             * Set and get quote id
             */
            setQuoteId: function (value) {
                this.quoteId = value;
            },
            getQuoteId: function () {
                return this.quoteId;
            },

            /**
             * Set and get display name
             */
            setDisplayName: function (value) {
                this.displayName = value;
            },
            getDisplayName: function () {
                return this.displayName;
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
             * Set and get grand total
             */
            setGrandTotalAmount: function (value) {
                this.grandTotalAmount = parseFloat(value).toFixed(2);
            },
            getGrandTotalAmount: function () {
                return parseFloat(this.grandTotalAmount);
            },

            /**
             * Set and get is logged in
             */
            setIsLoggedIn: function (value) {
                this.isLoggedIn = value;
            },
            getIsLoggedIn: function () {
                return this.isLoggedIn;
            },

            /**
             * Set and get store code
             */
            setStoreCode: function (value) {
                this.storeCode = value;
            },
            getStoreCode: function () {
                return this.storeCode;
            },

            /**
             * API Urls for logged in / guest
             */
            getApiUrl: function (uri) {
                if (this.getIsLoggedIn() === true) {
                    return "rest/" + this.getStoreCode() + "/V1/carts/mine/" + uri;
                } else {
                    return "rest/" + this.getStoreCode() + "/V1/guest-carts/" + this.getQuoteId() + "/" + uri;
                }
            },

            /**
             * Payment request info
             */
            getPaymentRequest: function () {
                return {
                    total: {
                        label: this.getDisplayName(),
                        amount: this.getGrandTotalAmount()
                    },
                    requiredShippingContactFields: ['postalAddress', 'name', 'email', 'phone'],
                    requiredBillingContactFields: ['postalAddress', 'name']
                };
            },

            /**
             * Retrieve shipping methods based on address
             */
            onShippingContactSelect: function (event, session) {
                // Get the address.
                let address = event.shippingContact;

                // Create a payload.
                let payload = {
                    address: {
                        city: address.locality,
                        region: address.administrativeArea,
                        country_id: address.countryCode.toUpperCase(),
                        postcode: address.postalCode,
                        save_in_address_book: 0
                    }
                };

                this.shippingAddress = payload.address;

                // POST to endpoint for shipping methods.
                storage.post(
                    this.getApiUrl("estimate-shipping-methods"),
                    JSON.stringify(payload)
                ).done(function (result) {
                    // Stop if no shipping methods.
                    let virtualFlag = false;
                    if (result.length === 0) {
                        let productItems = customerData.get('cart')().items;
                        _.each(productItems,
                            function (item) {
                                if (item.is_virtual || item.product_type == 'bundle') {
                                    virtualFlag = true;
                                } else {
                                    virtualFlag = false;
                                }
                            }
                        );
                        if (!virtualFlag) {
                            session.abort();
                            alert($t("There are no shipping methods available for you right now. Please try again or use an alternative payment method."));
                            return false;
                        }
                    }

                    let shippingMethods = [];
                    this.shippingMethods = {};

                    // Format shipping methods array.
                    for (let i = 0; i < result.length; i++) {
                        if (typeof result[i].method_code !== 'string') {
                            continue;
                        }

                        let method = {
                            identifier: result[i].method_code,
                            label: result[i].method_title,
                            detail: result[i].carrier_title ? result[i].carrier_title : "",
                            amount: parseFloat(result[i].amount).toFixed(2)
                        };

                        // Add method object to array.
                        shippingMethods.push(method);

                        this.shippingMethods[result[i].method_code] = result[i];

                        if (!this.shippingMethod) {
                            this.shippingMethod = result[i].method_code;
                        }
                    }

                    // Create payload to get totals
                    let totalsPayload = {
                        "addressInformation": {
                            "address": {
                                "countryId": this.shippingAddress.country_id,
                                "region": this.shippingAddress.region,
                                "regionId": this.getRegionId(this.shippingAddress.country_id, this.shippingAddress.region),
                                "postcode": this.shippingAddress.postcode
                            },
                            "shipping_method_code": virtualFlag ? null : this.shippingMethods[shippingMethods[0].identifier].method_code,
                            "shipping_carrier_code": virtualFlag ? null : this.shippingMethods[shippingMethods[0].identifier].carrier_code
                        }
                    };

                    // POST to endpoint to get totals, using 1st shipping method
                    storage.post(
                        this.getApiUrl("totals-information"),
                        JSON.stringify(totalsPayload)
                    ).done(function (result) {
                        // Set total
                        this.setGrandTotalAmount(result.base_grand_total);

                        // Pass shipping methods back
                        session.completeShippingContactSelection(
                            ApplePaySession.STATUS_SUCCESS,
                            shippingMethods,
                            {
                                label: this.getDisplayName(),
                                amount: this.getGrandTotalAmount()
                            },
                            [{
                                type: 'final',
                                label: $t('Shipping'),
                                amount: virtualFlag ? 0 : shippingMethods[0].amount
                            }]
                        );
                    }.bind(this)).fail(function (result) {
                        session.abort();
                        alert($t("We're unable to fetch the cart totals for you. Please try an alternative payment method."));
                        console.error("Braintree ApplePay: Unable to get totals", result);
                        return false;
                    });

                }.bind(this)).fail(function (result) {
                    session.abort();
                    alert($t("We're unable to find any shipping methods for you. Please try an alternative payment method."));
                    console.error("Braintree ApplePay: Unable to find shipping methods for estimate-shipping-methods", result);
                    return false;
                });
            },

            /**
             * Record which shipping method has been selected & Updated totals
             */
            onShippingMethodSelect: function (event, session) {
                let shippingMethod = event.shippingMethod;
                this.shippingMethod = shippingMethod.identifier;

                let payload = {
                    "addressInformation": {
                        "address": {
                            "countryId": this.shippingAddress.country_id,
                            "region": this.shippingAddress.region,
                            "regionId": this.getRegionId(this.shippingAddress.country_id, this.shippingAddress.region),
                            "postcode": this.shippingAddress.postcode
                        },
                        "shipping_method_code": this.shippingMethods[this.shippingMethod].method_code,
                        "shipping_carrier_code": this.shippingMethods[this.shippingMethod].carrier_code
                    }
                };

                storage.post(
                    this.getApiUrl("totals-information"),
                    JSON.stringify(payload)
                ).done(function (r) {
                    this.setGrandTotalAmount(r.base_grand_total);

                    session.completeShippingMethodSelection(
                        ApplePaySession.STATUS_SUCCESS,
                        {
                            label: this.getDisplayName(),
                            amount: this.getGrandTotalAmount()
                        },
                        [{
                            type: 'final',
                            label: $t('Shipping'),
                            amount: shippingMethod.amount
                        }]
                    );
                }.bind(this));
            },

            /**
             * Place the order
             */
            startPlaceOrder: function (nonce, event, session, device_data) {
                let shippingContact = event.payment.shippingContact,
                    billingContact = event.payment.billingContact,
                    payload = {
                        "addressInformation": {
                            "shipping_address": {
                                "email": shippingContact.emailAddress,
                                "telephone": shippingContact.phoneNumber,
                                "firstname": shippingContact.givenName,
                                "lastname": shippingContact.familyName,
                                "street": shippingContact.addressLines,
                                "city": shippingContact.locality,
                                "region": shippingContact.administrativeArea,
                                "region_id": this.getRegionId(shippingContact.countryCode.toUpperCase(), shippingContact.administrativeArea),
                                "region_code": null,
                                "country_id": shippingContact.countryCode.toUpperCase(),
                                "postcode": shippingContact.postalCode,
                                "same_as_billing": 0,
                                "customer_address_id": 0,
                                "save_in_address_book": 0
                            },
                            "billing_address": {
                                "email": shippingContact.emailAddress,
                                "telephone": shippingContact.phoneNumber,
                                "firstname": billingContact.givenName,
                                "lastname": billingContact.familyName,
                                "street": billingContact.addressLines,
                                "city": billingContact.locality,
                                "region": billingContact.administrativeArea,
                                "region_id": this.getRegionId(billingContact.countryCode.toUpperCase(), billingContact.administrativeArea),
                                "region_code": null,
                                "country_id": billingContact.countryCode.toUpperCase(),
                                "postcode": billingContact.postalCode,
                                "same_as_billing": 0,
                                "customer_address_id": 0,
                                "save_in_address_book": 0
                            },
                            "shipping_method_code": this.shippingMethod ? this.shippingMethods[this.shippingMethod].method_code : '' ,
                            "shipping_carrier_code": this.shippingMethod ? this.shippingMethods[this.shippingMethod].carrier_code : ''
                        }
                    };

                // Set addresses
                storage.post(
                    this.getApiUrl("shipping-information"),
                    JSON.stringify(payload)
                ).done(function () {
                    // Submit payment information
                    let paymentInformation = {
                            "email": shippingContact.emailAddress,
                            "paymentMethod": {
                                "method": "braintree_applepay",
                                "additional_data": {
                                    "payment_method_nonce": nonce,
                                    "device_data": device_data
                                }
                            }
                        };
                    if (window.checkout && window.checkout.agreementIds) {
                        paymentInformation.paymentMethod.extension_attributes = {
                            "agreement_ids": window.checkout.agreementIds
                        };
                    }
                    storage.post(
                        this.getApiUrl("payment-information"),
                        JSON.stringify(paymentInformation)
                    ).done(function (r) {
                        document.location = this.getActionSuccess();
                        session.completePayment(ApplePaySession.STATUS_SUCCESS);
                    }.bind(this)).fail(function (r) {
                        session.completePayment(ApplePaySession.STATUS_FAILURE);
                        session.abort();
                        alert($t("We're unable to take your payment through Apple Pay. Please try an again or use an alternative payment method."));
                        console.error("Braintree ApplePay Unable to take payment", r);
                        return false;
                    });

                }.bind(this)).fail(function (r) {
                    console.error("Braintree ApplePay Unable to set shipping information", r);
                    session.completePayment(ApplePaySession.STATUS_INVALID_BILLING_POSTAL_ADDRESS);
                });
            }
        });
    });
