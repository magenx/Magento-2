/**
 * Braintree Apple Pay mini cart payment method integration.
 **/
define(
    [
        'uiComponent',
        'PayPal_Braintree/js/applepay/button',
        'PayPal_Braintree/js/applepay/api',
        'mage/translate',
        'domReady!'
    ],
    function (
        Component,
        button,
        buttonApi,
        $t
    ) {
        'use strict';

        return Component.extend({

            defaults: {
                id: null,
                clientToken: null,
                quoteId: 0,
                displayName: null,
                actionSuccess: null,
                grandTotalAmount: 0,
                isLoggedIn: false,
                storeCode: "default"
            },

            /**
             * @returns {Object}
             */
            initialize: function () {
                this._super();
                if (!this.displayName) {
                    this.displayName = $t('Store');
                }

                var api = new buttonApi();
                api.setGrandTotalAmount(parseFloat(this.grandTotalAmount).toFixed(2));
                api.setClientToken(this.clientToken);
                api.setDisplayName(this.displayName);
                api.setQuoteId(this.quoteId);
                api.setActionSuccess(this.actionSuccess);
                api.setIsLoggedIn(this.isLoggedIn);
                api.setStoreCode(this.storeCode);

                // Attach the button
                button.init(
                    document.getElementById(this.id),
                    api
                );

                return this;
            }
        });
    }
);
