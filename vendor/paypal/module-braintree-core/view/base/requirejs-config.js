/**
 * Config to pull in all the relevant Braintree JS SDKs
 * @type {{paths: {braintreePayPalInContextCheckout: string, braintreePayPalCheckout: string, braintreeVenmo: string, braintreeHostedFields: string, braintreeDataCollector: string, braintreeThreeDSecure: string, braintreeGooglePay: string, braintreeApplePay: string, braintreeAch: string, braintreeLpm: string, googlePayLibrary: string}, map: {"*": {braintree: string}}}}
 */
var config = {
    map: {
        '*': {
            braintree: 'https://js.braintreegateway.com/web/3.88.1/js/client.min.js',
        }
    },

    paths: {
        "braintreePayPalCheckout": "https://js.braintreegateway.com/web/3.88.1/js/paypal-checkout.min",
        "braintreeHostedFields": "https://js.braintreegateway.com/web/3.88.1/js/hosted-fields.min",
        "braintreeDataCollector": "https://js.braintreegateway.com/web/3.88.1/js/data-collector.min",
        "braintreeThreeDSecure": "https://js.braintreegateway.com/web/3.88.1/js/three-d-secure.min",
        "braintreeApplePay": 'https://js.braintreegateway.com/web/3.88.1/js/apple-pay.min',
        "braintreeGooglePay": 'https://js.braintreegateway.com/web/3.88.1/js/google-payment.min',
        "braintreeVenmo": 'https://js.braintreegateway.com/web/3.88.1/js/venmo.min',
        "braintreeAch": "https://js.braintreegateway.com/web/3.88.1/js/us-bank-account.min",
        "braintreeLpm": "https://js.braintreegateway.com/web/3.88.1/js/local-payment.min",
        "googlePayLibrary": "https://pay.google.com/gp/p/js/pay",
        "braintreePayPalInContextCheckout": "https://www.paypalobjects.com/api/checkout"
    }
};
