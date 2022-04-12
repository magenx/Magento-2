/**
 * Copyright 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define([
    'jquery',
    'Magento_Ui/js/model/messageList',
    'PayPal_Braintree/js/view/payment/3d-secure',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, globalMessageList, verify3DSecure, fullScreenLoader) {
    'use strict';

    return {
        validators: [],

        /**
         * Get payment config
         * @returns {Object}
         */
        getConfig: function () {
            return window.checkoutConfig.payment;
        },

        /**
         * Init list of validators
         */
        initialize: function () {
            var config = this.getConfig();

            if (config[verify3DSecure.getCode()].enabled) {
                verify3DSecure.setConfig(config[verify3DSecure.getCode()]);
                this.add(verify3DSecure);
            }
        },

        /**
         * Add new validator
         * @param {Object} validator
         */
        add: function (validator) {
            this.validators.push(validator);
        },

        /**
         * Run pull of validators
         * @param {Object} context
         * @param {Function} callback
         */
        validate: function (context, callback, errorCallback) {
            var self = this,
                deferred;

            // no available validators
            if (!self.validators.length) {
                callback();

                return;
            }

            // get list of deferred validators
            deferred = $.map(self.validators, function (current) {
                return current.validate(context);
            });

            $.when.apply($, deferred)
                .done(function () {
                    callback();
                }).fail(function (error) {
                    errorCallback();
                    self.showError(error);
                });
        },

        /**
         * Show error message
         * @param {String} errorMessage
         */
        showError: function (errorMessage) {
            globalMessageList.addErrorMessage({
                message: errorMessage
            });
            fullScreenLoader.stopLoader(true);
        }
    };
});
