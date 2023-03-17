/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/view/payment/default',
    'braintree',
    'braintreeCheckoutPayPalAdapter',
    'braintreePayPalCheckout',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Vault/js/view/payment/vault-enabler',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_CheckoutAgreements/js/view/checkout-agreements',
    'mage/translate'
], function (
    $,
    _,
    Component,
    braintree,
    Braintree,
    paypalCheckout,
    quote,
    fullScreenLoader,
    additionalValidators,
    stepNavigator,
    VaultEnabler,
    createBillingAddress,
    selectBillingAddress,
    checkoutAgreements,
    $t
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PayPal_Braintree/payment/paypal',
            code: 'braintree_paypal',
            active: false,
            paypalInstance: null,
            paymentMethodNonce: null,
            grandTotalAmount: null,
            isReviewRequired: false,
            customerEmail: null,

            /**
             * Additional payment data
             *
             * {Object}
             */
            additionalData: {},

            /**
             * {Array}
             */
            lineItemsArray: [
                'name',
                'kind',
                'quantity',
                'unitAmount',
                'unitTaxAmount',
                'productCode',
                'description'
            ],

            /**
             * PayPal client configuration
             * {Object}
             */
            clientConfig: {
                offerCredit: false,
                offerCreditOnly: false,
                dataCollector: {
                    paypal: true
                },

                buttonPayPalId: 'braintree_paypal_placeholder',
                buttonCreditId: 'braintree_paypal_credit_placeholder',
                buttonPaylaterId: 'braintree_paypal_paylater_placeholder',

                onDeviceDataRecieved: function (deviceData) {
                    this.additionalData['device_data'] = deviceData;
                },

                /**
                 * Triggers when widget is loaded
                 * @param {Object} context
                 */
                onReady: function (context) {
                    this.setupPayPal();
                },

                /**
                 * Triggers on payment nonce receive
                 * @param {Object} response
                 */
                onPaymentMethodReceived: function (response) {
                    this.beforePlaceOrder(response);
                }
            },
            imports: {
                onActiveChange: 'active'
            }
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            var self = this;

            this._super()
                .observe(['active', 'isReviewRequired', 'customerEmail']);

            window.addEventListener('hashchange', function (e) {
                var methodCode = quote.paymentMethod();

                if (methodCode === 'braintree_paypal' || methodCode === 'braintree_paypal_vault') {
                    if (e.newURL.indexOf('payment') > 0 && self.grandTotalAmount !== null) {
                        self.reInitPayPal();
                    }
                }
            });

            quote.paymentMethod.subscribe(function (value) {
                var methodCode = value;

                if (methodCode === 'braintree_paypal' || methodCode === 'braintree_paypal_vault') {
                    self.reInitPayPal();
                }
            });

            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());
            this.vaultEnabler.isActivePaymentTokenEnabler.subscribe(function () {
                self.onVaultPaymentTokenEnablerChange();
            });

            this.grandTotalAmount = quote.totals()['base_grand_total'];

            quote.totals.subscribe(function () {
                if (self.grandTotalAmount !== quote.totals()['base_grand_total']) {
                    self.grandTotalAmount = quote.totals()['base_grand_total'];
                    var methodCode = quote.paymentMethod();

                    if (methodCode && (methodCode.method === 'braintree_paypal' || methodCode.method === 'braintree_paypal_vault')) {
                        self.reInitPayPal();
                    }
                }
            });

            // for each component initialization need update property
            this.isReviewRequired(false);
            this.initClientConfig();

            return this;
        },

        /**
         * Get payment name
         *
         * @returns {String}
         */
        getCode: function () {
            return this.code;
        },

        /**
         * Get payment title
         *
         * @returns {String}
         */
        getTitle: function () {
            return window.checkoutConfig.payment[this.getCode()].title;
        },

        /**
         * Check if payment is active
         *
         * @returns {Boolean}
         */
        isActive: function () {
            var active = this.getCode() === this.isChecked();

            this.active(active);

            return active;
        },

        /**
         * Triggers when payment method change
         * @param {Boolean} isActive
         */
        onActiveChange: function (isActive) {
            if (!isActive) {
                return;
            }

            // need always re-init Braintree with PayPal configuration
            this.reInitPayPal();
        },

        /**
         * Init config
         */
        initClientConfig: function () {
            this.clientConfig = _.extend(this.clientConfig, this.getPayPalConfig());

            _.each(this.clientConfig, function (fn, name) {
                if (typeof fn === 'function') {
                    this.clientConfig[name] = fn.bind(this);
                }
            }, this);
        },

        /**
         * Set payment nonce
         * @param {String} paymentMethodNonce
         */
        setPaymentMethodNonce: function (paymentMethodNonce) {
            this.paymentMethodNonce = paymentMethodNonce;
        },

        /**
         * Update quote billing address
         * @param {Object}customer
         * @param {Object}address
         */
        setBillingAddress: function (customer, address) {
            var billingAddress = {
                street: [address.line1],
                city: address.city,
                postcode: address.postalCode,
                countryId: address.countryCode,
                email: customer.email,
                firstname: customer.firstName,
                lastname: customer.lastName,
                telephone: typeof customer.phone !== 'undefined' ? customer.phone : '00000000000'
            };

            billingAddress['region_code'] = typeof address.state === 'string' ? address.state : '';
            billingAddress = createBillingAddress(billingAddress);
            quote.billingAddress(billingAddress);
        },

        /**
         * Prepare data to place order
         * @param {Object} data
         */
        beforePlaceOrder: function (data) {
            this.setPaymentMethodNonce(data.nonce);
            this.customerEmail(data.details.email);
            if (quote.isVirtual()) {
                this.isReviewRequired(true);
            } else {
                if (this.isRequiredBillingAddress() === '1' || quote.billingAddress() === null) {
                    if (typeof data.details.billingAddress !== 'undefined') {
                        this.setBillingAddress(data.details, data.details.billingAddress);
                    } else {
                        this.setBillingAddress(data.details, data.details.shippingAddress);
                    }
                } else {
                    if (quote.shippingAddress() === quote.billingAddress()) {
                        selectBillingAddress(quote.shippingAddress());
                    } else {
                        selectBillingAddress(quote.billingAddress());
                    }
                }
            }
            this.placeOrder();
        },

        /**
         * Re-init PayPal Auth Flow
         */
        reInitPayPal: function () {
            this.disableButton();
            this.clientConfig.paypal.amount = parseFloat(this.grandTotalAmount).toFixed(2);

            if (!quote.isVirtual()) {
                this.clientConfig.paypal.enableShippingAddress = true;
                this.clientConfig.paypal.shippingAddressEditable = false;
                this.clientConfig.paypal.shippingAddressOverride = this.getShippingAddress();
            }
            // Send Line Items
            this.clientConfig.paypal.lineItems = this.getLineItems();

            Braintree.setConfig(this.clientConfig);

            if (Braintree.getPayPalInstance()) {
                Braintree.getPayPalInstance().teardown(function () {
                    Braintree.setup();
                }.bind(this));
                Braintree.setPayPalInstance(null);
            } else {
                Braintree.setup();
                this.enableButton();
            }
        },

        /**
         * Setup PayPal instance
         */
        setupPayPal: function () {
            var self = this;

            if (Braintree.config.paypalInstance) {
                fullScreenLoader.stopLoader(true);
                return;
            }

            paypalCheckout.create({
                client: Braintree.clientInstance
            }, function (createErr, paypalCheckoutInstance) {
                if (createErr) {
                    Braintree.showError($t("PayPal Checkout could not be initialized. Please contact the store owner."));
                    console.error('paypalCheckout error', createErr);
                    return;
                }
                let quoteObj = quote.totals();

                var configSDK = {
                    components: 'buttons,messages,funding-eligibility',
                    "enable-funding": "paylater",
                    currency: quoteObj['base_currency_code']
                };
                var merchantCountry = window.checkoutConfig.payment['braintree_paypal'].merchantCountry;
                if (Braintree.getEnvironment() == 'sandbox' && merchantCountry != null) {
                    configSDK["buyer-country"] = merchantCountry;
                }
                paypalCheckoutInstance.loadPayPalSDK(configSDK, function () {
                    this.loadPayPalButton(paypalCheckoutInstance, 'paypal');
                    if (this.isCreditEnabled()) {
                        this.loadPayPalButton(paypalCheckoutInstance, 'credit');
                    }
                    if (this.isPaylaterEnabled()) {
                        this.loadPayPalButton(paypalCheckoutInstance, 'paylater');
                    }

                }.bind(this));
            }.bind(this));
        },

        loadPayPalButton: function (paypalCheckoutInstance, funding) {
            var paypalPayment = Braintree.config.paypal,
                onPaymentMethodReceived = Braintree.config.onPaymentMethodReceived;
            var style = {
                color: Braintree.getColor(funding),
                shape: Braintree.getShape(funding),
                size: Braintree.getSize(funding),
                label: Braintree.getLabel(funding)
            };

            if (Braintree.getBranding()) {
                style.branding = Braintree.getBranding();
            }
            if (Braintree.getFundingIcons()) {
                style.fundingicons = Braintree.getFundingIcons();
            }

            if (funding === 'credit') {
                Braintree.config.buttonId = this.clientConfig.buttonCreditId;
            } else if (funding === 'paylater') {
                Braintree.config.buttonId = this.clientConfig.buttonPaylaterId;
            } else {
                Braintree.config.buttonId = this.clientConfig.buttonPayPalId;
            }
            // Render
            Braintree.config.paypalInstance = paypalCheckoutInstance;
            var events = Braintree.events;
            $('#' + Braintree.config.buttonId).html('');

            var button = paypal.Buttons({
                fundingSource: funding,
                env: Braintree.getEnvironment(),
                style: style,
                commit: true,
                locale: Braintree.config.paypal.locale,

                onInit: function (data, actions) {
                    var agreements = checkoutAgreements().agreements,
                        shouldDisableActions = false;

                    actions.disable();

                    _.each(agreements, function (item, index) {
                        if (checkoutAgreements().isAgreementRequired(item)) {
                            var paymentMethodCode = quote.paymentMethod().method,
                                inputId = '#agreement_' + paymentMethodCode + '_' + item.agreementId,
                                inputEl = document.querySelector(inputId);


                            if (!inputEl.checked) {
                                shouldDisableActions = true;
                            }

                            inputEl.addEventListener('change', function (event) {
                                if (additionalValidators.validate()) {
                                    actions.enable();
                                } else {
                                    actions.disable();
                                }
                            });
                        }
                    });

                    if (!shouldDisableActions) {
                        actions.enable();
                    }
                },

                createOrder: function () {
                    return paypalCheckoutInstance.createPayment(paypalPayment).catch(function (err) {
                        throw err.details.originalError.details.originalError.paymentResource;
                    });
                },

                onCancel: function (data) {
                    console.log('checkout.js payment cancelled', JSON.stringify(data, 0, 2));

                    if (typeof events.onCancel === 'function') {
                        events.onCancel();
                    }
                },

                onError: function (err) {
                    if (err.errorName === 'VALIDATION_ERROR' && err.errorMessage.indexOf('Value is invalid') !== -1) {
                        Braintree.showError($t('Address failed validation. Please check and confirm your City, State, and Postal Code'));
                    } else {
                        Braintree.showError($t("PayPal Checkout could not be initialized. Please contact the store owner."));
                    }
                    Braintree.config.paypalInstance = null;
                    console.error('Paypal checkout.js error', err);

                    if (typeof events.onError === 'function') {
                        events.onError(err);
                    }
                }.bind(this),

                onClick: function (data) {
                    if (!quote.isVirtual()) {
                        this.clientConfig.paypal.enableShippingAddress = true;
                        this.clientConfig.paypal.shippingAddressEditable = false;
                        this.clientConfig.paypal.shippingAddressOverride = this.getShippingAddress();
                    }

                    // To check term & conditions input checked - validate additional validators.
                    if (!additionalValidators.validate()) {
                        return false;
                    }

                    if (typeof events.onClick === 'function') {
                        events.onClick(data);
                    }
                }.bind(this),

                onApprove: function (data, actions) {
                    return paypalCheckoutInstance.tokenizePayment(data)
                        .then(function (payload) {
                            onPaymentMethodReceived(payload);
                        });
                }

            });
            if (button.isEligible() && $('#' + Braintree.config.buttonId).length) {
                button.render('#' + Braintree.config.buttonId).then(function () {
                    Braintree.enableButton();
                    if (typeof Braintree.config.onPaymentMethodError === 'function') {
                        Braintree.config.onPaymentMethodError();
                    }
                }.bind(this)).then(function (data) {
                    if (typeof events.onRender === 'function') {
                        events.onRender(data);
                    }
                });
            }
        },

        /**
         * Get locale
         * @returns {String}
         */
        getLocale: function () {
            return window.checkoutConfig.payment[this.getCode()].locale;
        },

        /**
         * Is Billing Address required from PayPal side
         * @returns {exports.isRequiredBillingAddress|(function())|boolean}
         */
        isRequiredBillingAddress: function () {
            return window.checkoutConfig.payment[this.getCode()].isRequiredBillingAddress;
        },

        /**
         * Get configuration for PayPal
         * @returns {Object}
         */
        getPayPalConfig: function () {
            var totals = quote.totals(),
                config = {},
                isActiveVaultEnabler = this.isActiveVault();

            config.paypal = {
                flow: 'checkout',
                amount: parseFloat(this.grandTotalAmount).toFixed(2),
                currency: totals['base_currency_code'],
                locale: this.getLocale(),

                /**
                 * Triggers on any Braintree error
                 */
                onError: function () {
                    this.paymentMethodNonce = null;
                },

                /**
                 * Triggers if browser doesn't support PayPal Checkout
                 */
                onUnsupported: function () {
                    this.paymentMethodNonce = null;
                }
            };

            if (isActiveVaultEnabler) {
                config.paypal.requestBillingAgreement = true;
            }

            if (!quote.isVirtual()) {
                config.paypal.enableShippingAddress = true;
                config.paypal.shippingAddressEditable = false;
                config.paypal.shippingAddressOverride = this.getShippingAddress();
            }

            if (this.getMerchantName()) {
                config.paypal.displayName = this.getMerchantName();
            }

            return config;
        },

        /**
         * Get shipping address
         * @returns {Object}
         */
        getShippingAddress: function () {
            var address = quote.shippingAddress();

            return {
                recipientName: address.firstname + ' ' + address.lastname,
                line1: address.street[0],
                line2: typeof address.street[2] === 'undefined' ? address.street[1] : address.street[1] + ' ' + address.street[2],
                city: address.city,
                countryCode: address.countryId,
                postalCode: address.postcode,
                state: address.regionCode
            };
        },

        /**
         * Get merchant name
         * @returns {String}
         */
        getMerchantName: function () {
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
                    'payment_method_nonce': this.paymentMethodNonce
                }
            };

            data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

            this.vaultEnabler.visitAdditionalData(data);

            return data;
        },

        /**
         * Returns payment acceptance mark image path
         * @returns {String}
         */
        getPaymentAcceptanceMarkSrc: function () {
            return window.checkoutConfig.payment[this.getCode()].paymentAcceptanceMarkSrc;
        },

        /**
         * @returns {String}
         */
        getVaultCode: function () {
            return window.checkoutConfig.payment[this.getCode()].vaultCode;
        },

        /**
         * Check if need to skip order review
         * @returns {Boolean}
         */
        isSkipOrderReview: function () {
            return window.checkoutConfig.payment[this.getCode()].skipOrderReview;
        },

        /**
         * Checks if vault is active
         * @returns {Boolean}
         */
        isActiveVault: function () {
            return this.vaultEnabler.isVaultEnabled() && this.vaultEnabler.isActivePaymentTokenEnabler();
        },

        /**
         * Re-init PayPal Auth flow to use Vault
         */
        onVaultPaymentTokenEnablerChange: function () {
            this.clientConfig.paypal.singleUse = !this.isActiveVault();
            this.reInitPayPal();
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
            fullScreenLoader.stopLoader(true);
        },

        /**
         * Triggers when customer click "Continue to PayPal" button
         */
        payWithPayPal: function () {
            if (additionalValidators.validate()) {
                Braintree.checkout.paypal.initAuthFlow();
            }
        },

        /**
         * Get button id
         * @returns {String}
         */
        getPayPalButtonId: function () {
            return this.clientConfig.buttonPayPalId;
        },

        /**
         * Get button id
         * @returns {String}
         */
        getCreditButtonId: function () {
            return this.clientConfig.buttonCreditId;
        },

        /**
         * Get button id
         * @returns {String}
         */
        getPaylaterButtonId: function () {
            return this.clientConfig.buttonPaylaterId;
        },

        isPaylaterEnabled: function () {
            return window.checkoutConfig.payment['braintree_paypal_paylater']['isActive'];
        },

        isPaylaterMessageEnabled: function () {
            return window.checkoutConfig.payment['braintree_paypal_paylater']['isMessageActive'];
        },

        getGrandTotalAmount: function () {
            return parseFloat(this.grandTotalAmount).toFixed(2);
        },

        isCreditEnabled: function () {
            return window.checkoutConfig.payment['braintree_paypal_credit']['isActive'];
        },

        /**
         * Get Message Layout
         * @returns {*}
         */
        getMessagingLayout: function () {
            return window.checkoutConfig.payment['braintree_paypal_paylater']['message']['layout'];
        },

        /**
         * Get Message Logo
         * @returns {*}
         */
        getMessagingLogo: function () {
            return window.checkoutConfig.payment['braintree_paypal_paylater']['message']['logo'];
        },

        /**
         * Get Message Logo position
         * @returns {*}
         */
        getMessagingLogoPosition: function () {
            return window.checkoutConfig.payment['braintree_paypal_paylater']['message']['logo_position'];
        },

        /**
         * Get Message Text Color
         * @returns {*}
         */
        getMessagingTextColor: function () {
            return window.checkoutConfig.payment['braintree_paypal_paylater']['message']['text_color'];
        },

        /**
         * Get line items
         * @returns {Array}
         */
        getLineItems: function () {
            let self = this;
            let lineItems = [], storeCredit = 0, giftCardAccount = 0;
            let giftWrappingItems = 0, giftWrappingOrder = 0;
            $.each(quote.totals()['total_segments'], function(segmentsKey, segmentsItem) {
                if (segmentsItem['code'] === 'customerbalance') {
                    storeCredit = parseFloat(Math.abs(segmentsItem['value']).toString()).toFixed(2);
                }
                if (segmentsItem['code'] === 'giftcardaccount') {
                    giftCardAccount = parseFloat(Math.abs(segmentsItem['value']).toString()).toFixed(2);
                }
                if (segmentsItem['code'] === 'giftwrapping') {
                    let extensionAttributes = segmentsItem['extension_attributes'];
                    giftWrappingOrder = extensionAttributes['gw_base_price'];
                    giftWrappingItems = extensionAttributes['gw_items_base_price'];
                }
            });
            if (this.canSendLineItems()) {
                $.each(quote.getItems(), function(quoteItemKey, quoteItem) {
                    if (quoteItem.parent_item_id !== null || 0.0 === quoteItem.price) {
                        return true;
                    }

                    let itemName = self.replaceUnsupportedCharacters(quoteItem.name);
                    let itemSku = self.replaceUnsupportedCharacters(quoteItem.sku);

                    let description = '';
                    let itemQty = parseFloat(quoteItem.qty);
                    let itemUnitAmount = parseFloat(quoteItem.price);
                    if (itemQty > Math.floor(itemQty) && itemQty < Math.ceil(itemQty)) {
                        description = 'Item quantity is ' + itemQty.toFixed(2) + ' and per unit amount is ' + itemUnitAmount.toFixed(2);
                        itemUnitAmount = parseFloat(itemQty * itemUnitAmount);
                        itemQty = parseFloat('1');
                    }

                    let lineItemValues = [
                        itemName,
                        'debit',
                        itemQty.toFixed(2),
                        itemUnitAmount.toFixed(2),
                        parseFloat(quoteItem.base_tax_amount).toFixed(2),
                        itemSku,
                        description
                    ];

                    let mappedLineItems = $.map(self.lineItemsArray, function(itemElement, itemIndex) {
                        return [[
                            self.lineItemsArray[itemIndex],
                            lineItemValues[itemIndex]
                        ]]
                    });

                    lineItems[quoteItemKey] = Object.fromEntries(mappedLineItems);
                });

                /**
                 * Adds credit (refund or discount) kind as LineItems for the
                 * PayPal transaction if discount amount is greater than 0(Zero)
                 * as discountAmount lineItem field is not being used by PayPal.
                 *
                 * https://developer.paypal.com/braintree/docs/reference/response/transaction-line-item/php#discount_amount
                 */
                let baseDiscountAmount = parseFloat(Math.abs(quote.totals()['base_discount_amount']).toString()).toFixed(2);
                if (baseDiscountAmount > 0) {
                    let discountLineItem = {
                        'name': 'Discount',
                        'kind': 'credit',
                        'quantity': 1.00,
                        'unitAmount': baseDiscountAmount
                    };

                    lineItems = $.merge(lineItems, [discountLineItem]);
                }

                /**
                 * Adds shipping as LineItems for the PayPal transaction
                 * if shipping amount is greater than 0(Zero) to manage
                 * the totals with client-side implementation as there is
                 * no any field exist in the client-side implementation
                 * to send the shipping amount to the Braintree.
                 */
                if (quote.totals()['base_shipping_amount'] > 0) {
                    let shippingLineItem = {
                        'name': 'Shipping',
                        'kind': 'debit',
                        'quantity': 1.00,
                        'unitAmount': quote.totals()['base_shipping_amount']
                    };

                    lineItems = $.merge(lineItems, [shippingLineItem]);
                }

                /**
                 * Adds credit (Store Credit) kind as LineItems for the
                 * PayPal transaction if store credit is greater than 0(Zero)
                 * to manage the totals with client-side implementation
                 */
                if (storeCredit > 0) {
                    let storeCreditItem = {
                        'name': 'Store Credit',
                        'kind': 'credit',
                        'quantity': 1.00,
                        'unitAmount': storeCredit
                    };

                    lineItems = $.merge(lineItems, [storeCreditItem]);
                }

                /**
                 * Adds Gift Wrapping for items as LineItems for the PayPal
                 * transaction if it is greater than 0(Zero) to manage
                 * the totals with client-side implementation
                 */
                if (giftWrappingItems > 0) {
                    let gwItems = {
                        'name': 'Gift Wrapping for Items',
                        'kind': 'debit',
                        'quantity': 1.00,
                        'unitAmount': giftWrappingItems
                    };

                    lineItems = $.merge(lineItems, [gwItems]);
                }

                /**
                 * Adds Gift Wrapping for order as LineItems for the PayPal
                 * transaction if it is greater than 0(Zero) to manage
                 * the totals with client-side implementation
                 */
                if (giftWrappingOrder > 0) {
                    let gwOrderItem = {
                        'name': 'Gift Wrapping for Order',
                        'kind': 'debit',
                        'quantity': 1.00,
                        'unitAmount': giftWrappingOrder
                    };

                    lineItems = $.merge(lineItems, [gwOrderItem]);
                }

                /**
                 * Adds Gift Cards as credit LineItems for the PayPal
                 * transaction if it is greater than 0(Zero) to manage
                 * the totals with client-side implementation
                 */
                if (giftCardAccount > 0) {
                    let giftCardItem = {
                        'name': 'Gift Cards',
                        'kind': 'credit',
                        'quantity': 1.00,
                        'unitAmount': giftCardAccount
                    };

                    lineItems = $.merge(lineItems, [giftCardItem]);
                }

                if (lineItems.length >= 250) {
                    lineItems = [];
                }
            }
            return lineItems;
        },

        /**
         * Regex to replace all unsupported characters.
         *
         * @param str
         */
        replaceUnsupportedCharacters: function (str) {
            str.replace('/[^a-zA-Z0-9\s\-.\']/', '');
            return str.substr(0, 127);
        },

        /**
         * Can send line items
         *
         * @returns {Boolean}
         */
        canSendLineItems: function () {
            return window.checkoutConfig.payment[this.getCode()].canSendLineItems;
        }
    });
});
