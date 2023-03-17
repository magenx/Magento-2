require(['jquery', 'Magento_Ui/js/modal/alert', 'mage/translate', 'domReady!'], function ($, alert, $t) {
    function disablePayLaterMessages()
    {
        let merchantCountry = $('[data-ui-id="adminhtml-system-config-field-country-0-select-groups-account-fields-merchant-country-value"]').val();
        let payPalCredit = $('[data-ui-id="select-groups-braintree-section-groups-braintree-fields-braintree-paypal-credit-active-value"]').val();
        let cart = $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-button-cart-fields-message-cart-enable-value"]');
        let product = $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-button-checkout-fields-message-checkout-enable-value"]')
        let checkout = $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-button-productpage-fields-message-productpage-enable-value"]')
        let allowedCountries = ['GB', 'FR', 'US', 'DE', 'AU'];

        if($.inArray(merchantCountry, allowedCountries) === -1 || payPalCredit === 1){
            //hide pay later message
            cart.val(0).attr('readonly',true).click();
            product.val(0).attr('readonly',true).click();
            checkout.val(0).attr('readonly',true).click();
        }
        if (merchantCountry) {
            if ( merchantCountry === 'GB') {
                merchantCountry = 'UK'
            }
            cart.next().find('a').attr('href', cart.next().find('a').attr('href') + merchantCountry.toLowerCase());
            product.next().find('a').attr('href', product.next().find('a').attr('href') + merchantCountry.toLowerCase());
            checkout.next().find('a').attr('href', checkout.next().find('a').attr('href') + merchantCountry.toLowerCase());
        }

    }

    window.braintreeValidator = function (endpoint, environmentId, skip = false) {
        environmentId = $('[data-ui-id="' + environmentId + '"]').val();

        let merchantId = '', publicId = '', privateId = '';

        if (environmentId === 'sandbox') {
            merchantId = $('[data-ui-id="text-groups-braintree-section-groups-braintree-groups-braintree-required-fields-sandbox-merchant-id-value"]').val();
            publicId = $('[data-ui-id="password-groups-braintree-section-groups-braintree-groups-braintree-required-fields-sandbox-public-key-value"]').val();
            privateId = $('[data-ui-id="password-groups-braintree-section-groups-braintree-groups-braintree-required-fields-sandbox-private-key-value"]').val();
        } else {
            merchantId = $('[data-ui-id="text-groups-braintree-section-groups-braintree-groups-braintree-required-fields-merchant-id-value"]').val();
            publicId = $('[data-ui-id="password-groups-braintree-section-groups-braintree-groups-braintree-required-fields-public-key-value"]').val();
            privateId = $('[data-ui-id="password-groups-braintree-section-groups-braintree-groups-braintree-required-fields-private-key-value"]').val();
        }

        /* Remove previous success message if present */
        if ($(".braintree-credentials-success-message")) {
            $(".braintree-credentials-success-message").remove();
        }

        /* Basic field validation */
        var errors = [];

        if (!environmentId || environmentId !== 'sandbox' && environmentId !== 'production') {
            errors.push($t("Please select an Environment"));
        }

        if (!merchantId) {
            errors.push($t("Please enter a Merchant ID"));
        }

        if (!publicId) {
            errors.push($t('Please enter a Public Key'));
        }

        if (!privateId) {
            errors.push($t('Please enter a Private Key'));
        }

        if (errors.length > 0) {
            alert({
                title: $t('Braintree Credential Validation Failed'),
                content:  errors.join('<br />')
            });
            return false;
        }

        $(this).text($t("We're validating your credentials...")).attr('disabled', true);

        var self = this;
        $.ajax({
            type: 'POST',
            url: endpoint,
            data: {
                environment: environmentId,
                merchant_id: merchantId,
                public_key: publicId,
                private_key: privateId
            },
            showLoader: true,
            success: function (result) {
                if (result.success === 'true') {
                    if (skip === true) {
                        $('<div class="message message-success braintree-credentials-success-message">' + $t("Your credentials are valid.") + '</div>').insertAfter($('.paypal-styling-buttons'));
                    } else {
                        $('<div class="message message-success braintree-credentials-success-message">' + $t("Your credentials are valid.") + '</div>').insertAfter(self);
                    }
                } else {
                    alert({
                        title: $t('Braintree Credential Validation Failed'),
                        content: $t('Your Braintree Credentials could not be validated. Please ensure you have selected the correct environment and entered a valid Merchant ID, Public Key and Private Key.')
                    });
                }
            }
        }).always(function () {
            $(self).text($t("Validate Credentials")).attr('disabled', false);
        });
    };

    window.applyForAll = function () {
        let buttonShowStatus = '', buttonLabel = '', buttonColor = '', buttonShape = '', buttonSize = '';
        let locations = ['checkout', 'productpage', 'cart'], buttonTypes = ['paypal', 'paylater', 'credit'];

        let location = $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-fields-payment-location-value"]').val();
        let buttonType = $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + location + '-fields-paypal-location-' + location + '-button-type-value"]').val();
        buttonShowStatus = $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + location + '-groups-button-location-' + location + '-type-' + buttonType + '-fields-button-location-' + location + '-type-' + buttonType + '-show-value"]').val();
        buttonLabel = $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + location + '-groups-button-location-' + location + '-type-' + buttonType + '-fields-button-location-' + location + '-type-' + buttonType + '-label-value"]').val();
        buttonColor = $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + location + '-groups-button-location-' + location + '-type-' + buttonType + '-fields-button-location-' + location + '-type-' + buttonType + '-color-value"]').val();
        buttonShape = $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + location + '-groups-button-location-' + location + '-type-' + buttonType + '-fields-button-location-' + location + '-type-' + buttonType + '-shape-value"]').val();
        buttonSize = $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + location + '-groups-button-location-' + location + '-type-' + buttonType + '-fields-button-location-' + location + '-type-' + buttonType + '-size-value"]').val();

        // pay later messaging styling field values
        let messagingShow = $('.' + location + '-messaging-show').val();
        let messagingLayout = $('.' + location + '-messaging-layout').val();
        let messagingLogo = $('.' + location + '-messaging-logo').val();
        let messagingLogoPosition = $('.' + location + '-messaging-logo-position').val();
        let messagingTextColor = $('.' + location + '-messaging-text-color').val();

        locations.each(function (loc) {
            buttonTypes.each(function (type) {
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-show-value"]').val(buttonShowStatus).click();
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-label-value"]').val(buttonLabel).click();
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-color-value"]').val(buttonColor).click();
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-shape-value"]').val(buttonShape).click();
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-size-value"]').val(buttonSize).click();
            });

            // apply pay later messaging styling for all locations
            $('.' + loc + '-messaging-show').val(messagingShow).click();
            $('.' + loc + '-messaging-layout').val(messagingLayout).click();
            $('.' + loc + '-messaging-logo').val(messagingLogo).click();
            $('.' + loc + '-messaging-logo-position').val(messagingLogoPosition).click();
            $('.' + loc + '-messaging-text-color').val(messagingTextColor).click();
        });
        $('#save').click();
    };

    window.resetAll = function () {
        let locations = ['checkout', 'productpage', 'cart'], buttonTypes = ['paypal', 'paylater', 'credit'];
        let buttonShowStatus = 1, buttonLabel = 'paypal', buttonColor = 'gold', buttonShape = 'rect', buttonSize = 'responsive';

        locations.each(function (loc) {
            buttonTypes.each(function (type) {
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-show-value"]').val(buttonShowStatus).click();
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-label-value"]').val(buttonLabel).click();
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-color-value"]').val(buttonColor).click();
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-shape-value"]').val(buttonShape).click();
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-size-value"]').val(buttonSize).click();
            });

            // reset pay later messaging styling to recommended defaults
            $('.' + loc + '-messaging-show').val(1).click();
            $('.' + loc + '-messaging-layout').val('text').click();
            $('.' + loc + '-messaging-logo').val('inline').click();
            $('.' + loc + '-messaging-logo-position').val('left').click();
            $('.' + loc + '-messaging-text-color').val('black').click();
        });
        $('#save').click();
    };

    window.applyButton = function () {
        let locations = ['checkout', 'productpage', 'cart'], buttonTypes = ['paypal', 'paylater', 'credit'];

        locations.each(function (loc) {
            buttonTypes.each(function (type) {
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-show-value"]').click();
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-label-value"]').click();
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-color-value"]').click();
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-shape-value"]').click();
                $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-' + loc + '-groups-button-location-' + loc + '-type-' + type + '-fields-button-location-' + loc + '-type-' + type + '-size-value"]').click();
            });

            // apply pay later messaging styling to current location
            $('.' + loc + '-messaging-show').click();
            $('.' + loc + '-messaging-layout').click();
            $('.' + loc + '-messaging-logo').click();
            $('.' + loc + '-messaging-logo-position').click();
            $('.' + loc + '-messaging-text-color').click();
        });
        $('#save').click();
    };

    var locations = ['checkout', 'productpage', 'cart'];
    hidePaypalSections();
    $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-fields-payment-location-value"]').change(function () {
        hidePaypalSections();
    });
    locations.each(function (loc) {
        $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-'+loc+'-fields-paypal-location-'+loc+'-button-type-value"]').change(function () {
            hidePaypalSections();
        });
    });

    function hidePaypalSections() {
        var mainLocation, merchantCountryIndex, mainType;
        var locations = ['checkout', 'productpage', 'cart'], buttonTypes = ['paypal', 'paylater', 'credit'];
        mainLocation = $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-fields-payment-location-value"]');
        if (mainLocation.length < 1) {
            return false;
        }
        merchantCountryIndex = mainLocation.attr('id').split('_')[1];
        mainType = $('[data-ui-id="select-groups-braintree-section-groups-braintree-groups-braintree-paypal-groups-styling-groups-button-'+mainLocation.val()+'-fields-paypal-location-'+mainLocation.val()+'-button-type-value"]');
        locations.each(function (loc) {
            $('#row_payment_' + merchantCountryIndex + '_braintree_section_braintree_braintree_paypal_styling_button_' + loc).hide();
            buttonTypes.each(function (type) {
                $('#row_payment_'+merchantCountryIndex+'_braintree_section_braintree_braintree_paypal_styling_button_'+loc+'_button_location_'+loc+'_type_' + type).hide();
            });
        });
        $('#row_payment_'+merchantCountryIndex+'_braintree_section_braintree_braintree_paypal_styling_button_'+mainLocation.val()+'_button_location_'+mainLocation.val()+'_type_' + mainType.val()).show();
        $('#row_payment_'+merchantCountryIndex+'_braintree_section_braintree_braintree_paypal_styling_button_' + mainLocation.val()).show();
    }
    disablePayLaterMessages();
});
