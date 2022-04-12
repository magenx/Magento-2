define([
    'mage/utils/wrapper',
    'jquery'
], function (wrapper, $) {
    'use strict';

    let mixin = {
        handleHash: function (originalFn) {
            var hashString = window.location.hash.replace('#', '');
            if (hashString.indexOf('venmo') > -1) {
                return false;
            }

            return originalFn();
        }
    };

    return function (target) {
        return wrapper.extend(target, mixin);
    };
});
