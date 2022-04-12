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
                type: 'braintree_local_payment',
                component: 'PayPal_Braintree/js/view/payment/method-renderer/lpm'
            }
        );

        return Component.extend({});
    }
);
