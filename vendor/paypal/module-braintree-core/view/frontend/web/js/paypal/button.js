/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'rjsResolver',
        'uiRegistry',
        'uiComponent',
        'underscore',
        'jquery',
        'Magento_Customer/js/customer-data',
        'mage/translate',
        'braintree',
        'braintreeDataCollector',
        'braintreePayPalCheckout',
        'PayPal_Braintree/js/form-builder',
        'domReady!'
    ],
    function (
        resolver,
        registry,
        Component,
        _,
        $,
        customerData,
        $t,
        braintree,
        dataCollector,
        paypalCheckout,
        formBuilder
    ) {
        'use strict';
        let buttonIds = [];

        return {
            events: {
                onClick: null,
                onCancel: null,
                onError: null
            },

            /**
             * @param token
             * @param currency
             * @param env
             * @param local
             * @param lineItems
             */
            init: function (token, currency, env, local, lineItems) {
                if ($('.action-braintree-paypal-message').length) {
                    $('.product-add-form form').on('keyup change paste', 'input, select, textarea', function () {
                        var currentPrice, currencySymbol;
                        currentPrice = $(".product-info-main span").find("[data-price-type='finalPrice']").text();
                        currencySymbol = $('.action-braintree-paypal-message[data-pp-type="product"]').data('currency-symbol');
                        $('.action-braintree-paypal-message[data-pp-type="product"]').attr('data-pp-amount', currentPrice.replace(currencySymbol,''));
                    });
                }

                buttonIds = [];
                $('.action-braintree-paypal-logo').each(function () {
                    if (!$(this).hasClass("button-loaded")) {
                        $(this).addClass('button-loaded');
                        buttonIds.push($(this).attr('id'));
                    }
                });

                if (buttonIds.length > 0) {
                    this.loadSDK(token, currency, env, local, lineItems);
                }
            },

            /**
             * Load Braintree PayPal SDK
             * @param token
             * @param currency
             * @param env
             * @param local
             * @param lineItems
             */
            loadSDK: function (token, currency, env, local, lineItems) {
                braintree.create({
                    authorization: token
                }, function (clientErr, clientInstance) {
                    if (clientErr) {
                        console.error('paypalCheckout error', clientErr);
                        return this.showError("PayPal Checkout could not be initialized. Please contact the store owner.");
                    }
                    dataCollector.create({
                        client: clientInstance,
                        paypal: true
                    }, function (err, dataCollectorInstance) {
                        if (err) {
                            return console.log(err);
                        }
                    });
                    paypalCheckout.create({
                        client: clientInstance
                    }, function (err, paypalCheckoutInstance) {
                        if (typeof paypal !== 'undefined' ) {
                            this.renderPayPalButtons(buttonIds, paypalCheckoutInstance, lineItems);
                            this.renderPayPalMessages();
                        } else {
                            var configSDK = {
                                components: 'buttons,messages,funding-eligibility',
                                "enable-funding": "paylater",
                                currency: currency
                            };
                            if (env === 'sandbox' && local !== '') {
                                configSDK["buyer-country"] = local;
                            }
                            paypalCheckoutInstance.loadPayPalSDK(configSDK, function () {
                                this.renderPayPalButtons(buttonIds, paypalCheckoutInstance, lineItems);
                                this.renderPayPalMessages();
                            }.bind(this));
                        }
                    }.bind(this));
                }.bind(this));
            },

            /**
             * Render PayPal buttons
             *
             * @param ids
             * @param paypalCheckoutInstance
             * @param lineItems
             */
            renderPayPalButtons: function (ids, paypalCheckoutInstance, lineItems) {
                _.each(ids, function (id) {
                    this.payPalButton(id, paypalCheckoutInstance, lineItems);
                }.bind(this));
            },

            /**
             * Render PayPal messages
             */
            renderPayPalMessages: function () {
                $('.action-braintree-paypal-message').each(function () {
                    paypal.Messages({
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
                    }).render('#' + $(this).attr('id'));


                });
            },

            /**
             * @param id
             * @param paypalCheckoutInstance
             * @param lineItems
             */
            payPalButton: function (id, paypalCheckoutInstance, lineItems) {
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
                var paypalActions;
                var button = paypal.Buttons({
                    fundingSource: data.data('funding'),
                    style: style,
                    createOrder: function () {
                        return paypalCheckoutInstance.createPayment({
                            amount: data.data('amount'),
                            locale: data.data('locale'),
                            currency: data.data('currency'),
                            flow: 'checkout',
                            enableShippingAddress: true,
                            displayName: data.data('displayname'),
                            lineItems: $.parseJSON(lineItems)
                        });
                    },
                    validate: function (actions) {
                        var cart = customerData.get('cart'),
                            customer = customerData.get('customer'),
                            declinePayment = false,
                            isGuestCheckoutAllowed;
                        isGuestCheckoutAllowed = cart().isGuestCheckoutAllowed;
                        declinePayment = !customer().firstname && !isGuestCheckoutAllowed;
                        if (declinePayment) {
                            actions.disable();
                        }
                        paypalActions = actions;
                    },

                    onCancel: function (data) {
                        jQuery("#maincontent").trigger('processStop');
                    },

                    onError: function (err) {
                        console.error('paypalCheckout button render error', err);
                        jQuery("#maincontent").trigger('processStop');
                    },

                    onClick: function (data) {
                        var cart = customerData.get('cart'),
                            customer = customerData.get('customer'),
                            declinePayment = false,
                            isGuestCheckoutAllowed;

                        isGuestCheckoutAllowed = cart().isGuestCheckoutAllowed;
                        declinePayment = !customer().firstname && !isGuestCheckoutAllowed && (typeof isGuestCheckoutAllowed !== 'undefined');
                        if (declinePayment) {
                            alert($t('To check out, please sign in with your email address.'));
                        }
                    },

                    onApprove: function (data1) {
                        return paypalCheckoutInstance.tokenizePayment(data1, function (err, payload) {
                            jQuery("#maincontent").trigger('processStart');

                            // Map the shipping address correctly
                            var address = payload.details.shippingAddress;
                            var recipientFirstName, recipientLastName;
                            if (typeof address.recipientName !== 'undefined') {
                                var recipientName = address.recipientName.split(" ");
                                recipientFirstName = recipientName[0].replace(/'/g, "&apos;");
                                recipientLastName = recipientName[1].replace(/'/g, "&apos;");
                            } else {
                                recipientFirstName = payload.details.firstName.replace(/'/g, "&apos;");
                                recipientLastName = payload.details.lastName.replace(/'/g, "&apos;");
                            }
                            payload.details.shippingAddress = {
                                streetAddress: typeof address.line2 !== 'undefined' ? address.line1.replace(/'/g, "&apos;") + " " + address.line2.replace(/'/g, "&apos;") : address.line1.replace(/'/g, "&apos;"),
                                locality: address.city.replace(/'/g, "&apos;"),
                                postalCode: address.postalCode,
                                countryCodeAlpha2: address.countryCode,
                                email: payload.details.email.replace(/'/g, "&apos;"),
                                recipientFirstName: recipientFirstName,
                                recipientLastName: recipientLastName,
                                telephone: typeof payload.details.phone !== 'undefined' ? payload.details.phone : '',
                                region: typeof address.state !== 'undefined' ? address.state.replace(/'/g, "&apos;") : ''
                            };

                            payload.details.email = payload.details.email.replace(/'/g, "&apos;");
                            payload.details.firstName = payload.details.firstName.replace(/'/g, "&apos;");
                            payload.details.lastName = payload.details.lastName.replace(/'/g, "&apos;");
                            if (typeof payload.details.businessName !== 'undefined') {
                                payload.details.businessName = payload.details.businessName.replace(/'/g, "&apos;");
                            }

                            // Map the billing address correctly
                            let isRequiredBillingAddress = data.data('requiredbillingaddress');
                            if ((isRequiredBillingAddress === 1) && (typeof payload.details.billingAddress !== 'undefined')) {
                                var billingAddress = payload.details.billingAddress;
                                payload.details.billingAddress = {
                                    streetAddress: typeof billingAddress.line2 !== 'undefined' ? billingAddress.line1.replace(/'/g, "&apos;") + " " + billingAddress.line2.replace(/'/g, "&apos;") : billingAddress.line1.replace(/'/g, "&apos;"),
                                    locality: billingAddress.city.replace(/'/g, "&apos;"),
                                    postalCode: billingAddress.postalCode,
                                    countryCodeAlpha2: billingAddress.countryCode,
                                    telephone: typeof payload.details.phone !== 'undefined' ? payload.details.phone : '',
                                    region: typeof billingAddress.state !== 'undefined' ? billingAddress.state.replace(/'/g, "&apos;") : ''
                                };
                            }

                            if (data.data('location') == 'productpage') {
                                var form = $("#product_addtocart_form");
                                if (!(form.validation() && form.validation('isValid'))) {
                                    return false;
                                }
                                payload.additionalData = form.serialize();
                            }

                            var actionSuccess = data.data('actionsuccess');
                            formBuilder.build(
                                {
                                    action: actionSuccess,
                                    fields: {
                                        result: JSON.stringify(payload)
                                    }
                                }
                            ).submit();
                        });
                    }
                });
                if (!button.isEligible()) {
                    console.log('PayPal button is not elligible')
                    data.parent().remove();
                    return;
                }
                if ($('#' + data.attr('id')).length) {
                    button.render('#' + data.attr('id'));
                }
            },
        }
    }
);
