define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'braintree_ach_direct_debit',
                component: 'PayPal_Braintree/js/view/payment/method-renderer/ach'
            }
        );

        return Component.extend({});
    }
);
