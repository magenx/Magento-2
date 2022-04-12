define(
    ['PayPal_Braintree/js/paypal/button', 'jquery'],
    function (button, $) {
        'use strict';

        return button.extend({

            defaults: {
                label: 'buynow',
                branding: true,
            },

            /**
             * The validation on the add-to-cart form is done after the PayPal window has opened.
             * This is because the validate method exposed by the PP Button requires an event to disable/enable the button.
             * We can't fire an event due to the way the mage.validation widget works and we can't do something gross like
             * an interval because the validation() method shows the error messages and focuses the user's input on the
             * first erroring input field.
             * @param payload
             * @returns {*}
             */
            beforeSubmit: function (payload) {
                var form = $("#product_addtocart_form");

                if (!(form.validation() && form.validation('isValid'))) {
                    return false;
                }

                payload.additionalData = form.serialize();

                return payload;
            }
        });
    }
);