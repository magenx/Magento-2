/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    let config = window.checkoutConfig.payment,
        braintreeType = 'braintree',
        payPalType = 'braintree_paypal',
        braintreeAchDirectDebit = 'braintree_ach_direct_debit',
        braintreeVenmo = 'braintree_venmo',
        braintreeLocalPayment = 'braintree_local_payment';

    if (config[braintreeType] && config[braintreeType].isActive && config[braintreeType].clientToken) {
        rendererList.push({
            type: braintreeType,
            component: 'PayPal_Braintree/js/view/payment/method-renderer/hosted-fields'
        });
    }

    if (config[payPalType] && config[payPalType].isActive) {
        rendererList.push({
            type: payPalType,
            component: 'PayPal_Braintree/js/view/payment/method-renderer/paypal'
        });
    }

    if (config[braintreeVenmo] && config[braintreeVenmo].isAllowed && config[braintreeVenmo].clientToken) {
        rendererList.push({
            type: braintreeVenmo,
            component: 'PayPal_Braintree/js/view/payment/method-renderer/venmo'
        });
    }

    if (config[braintreeAchDirectDebit] && config[braintreeAchDirectDebit].isActive && config[braintreeAchDirectDebit].clientToken) {
        rendererList.push({
            type: braintreeAchDirectDebit,
            component: 'PayPal_Braintree/js/view/payment/method-renderer/ach'
        });
    }

    if (config[braintreeLocalPayment] && config[braintreeLocalPayment].clientToken) {
        rendererList.push({
            type: braintreeLocalPayment,
            component: 'PayPal_Braintree/js/view/payment/method-renderer/lpm'
        });
    }

    /** Add view logic here if needed */
    return Component.extend({});
});
