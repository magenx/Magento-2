/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require([
    'underscore',
    'jquery',
    'domReady!'
], function (_, $) {
    'use strict';
    let buttonIds = [], currentButtonId = '';
    let location = '', buttonType = '', buttonShow = '', buttonLabel = '', buttonColor = '', buttonShape = '', buttonSize = '';
    let messagingShow = '', messagingLayout = '', messagingLogo = '', messagingLogoPosition = '', messagingTextColor = '';

    function getCurrentLocationAndButtonType()
    {
        location = $('.payment-location').val();
        buttonType = $('.' + location + '-button-type').val();
    }

    $(document).ready(function () {
        getCurrentLocationAndButtonType();

        $('.payment-location').on('change', function (customEvent) {
            location = $(this).val();
            buttonType = $('.' + location + '-button-type').val();
            buttonShow = $('.' + location + '-' + buttonType + '-show').val();
            buttonLabel = $('.' + location + '-' + buttonType + '-label').val();
            buttonColor = $('.' + location + '-' + buttonType + '-color').val();
            buttonShape = $('.' + location + '-' + buttonType + '-shape').val();
            buttonSize = $('.' + location + '-' + buttonType + '-size').val();

            updatePayPalButtonStyling(location, buttonType, buttonShow, buttonLabel, buttonColor, buttonShape, buttonSize);

            // render pay later messages when location changed
            messagingShow = $('.' + location + '-messaging-show').val();
            messagingLayout = $('.' + location + '-messaging-layout').val();
            messagingLogo = $('.' + location + '-messaging-logo').val();
            messagingLogoPosition = $('.' + location + '-messaging-logo-position').val();
            messagingTextColor = $('.' + location + '-messaging-text-color').val();

            renderPayLaterMessages(location, messagingShow, messagingLayout, messagingLogo, messagingLogoPosition, messagingTextColor);
            customEvent.stopImmediatePropagation();
        });

        $("select").change(function () {
            $(document).on('change', '.' + location + '-button-type', function (customEvent) {
                buttonType = $(this).val();
                buttonShow = $('.' + location + '-' + buttonType + '-show').val();
                buttonLabel = $('.' + location + '-' + buttonType + '-label').val();
                buttonColor = $('.' + location + '-' + buttonType + '-color').val();
                buttonShape = $('.' + location + '-' + buttonType + '-shape').val();
                buttonSize = $('.' + location + '-' + buttonType + '-size').val();

                updatePayPalButtonStyling(location, buttonType, buttonShow, buttonLabel, buttonColor, buttonShape, buttonSize);
                customEvent.stopImmediatePropagation();
            });

            $(document).on('change', '.' + location + '-' + buttonType + '-show', function (customEvent) {
                buttonShow = $(this).val();
                buttonLabel = $('.' + location + '-' + buttonType + '-label').val();
                buttonColor = $('.' + location + '-' + buttonType + '-color').val();
                buttonShape = $('.' + location + '-' + buttonType + '-shape').val();
                buttonSize = $('.' + location + '-' + buttonType + '-size').val();

                updatePayPalButtonStyling(location, buttonType, buttonShow, buttonLabel, buttonColor, buttonShape, buttonSize);
                customEvent.stopImmediatePropagation();
            });


            $(document).on('change', '.' + location + '-' + buttonType + '-label', function (customEvent) {
                buttonLabel = $(this).val();
                buttonShow = $('.' + location + '-' + buttonType + '-show').val();
                buttonColor = $('.' + location + '-' + buttonType + '-color').val();
                buttonShape = $('.' + location + '-' + buttonType + '-shape').val();
                buttonSize = $('.' + location + '-' + buttonType + '-size').val();

                updatePayPalButtonStyling(location, buttonType, buttonShow, buttonLabel, buttonColor, buttonShape, buttonSize);
                customEvent.stopImmediatePropagation();
            });

            $(document).on('change', '.' + location + '-' + buttonType + '-color', function (customEvent) {
                buttonColor = $(this).val();
                buttonShow = $('.' + location + '-' + buttonType + '-show').val();
                buttonLabel = $('.' + location + '-' + buttonType + '-label').val();
                buttonShape = $('.' + location + '-' + buttonType + '-shape').val();
                buttonSize = $('.' + location + '-' + buttonType + '-size').val();

                updatePayPalButtonStyling(location, buttonType, buttonShow, buttonLabel, buttonColor, buttonShape, buttonSize);
                customEvent.stopImmediatePropagation();
            });

            $(document).on('change', '.' + location + '-' + buttonType + '-shape', function (customEvent) {
                buttonShape = $(this).val();
                buttonShow = $('.' + location + '-' + buttonType + '-show').val();
                buttonLabel = $('.' + location + '-' + buttonType + '-label').val();
                buttonColor = $('.' + location + '-' + buttonType + '-color').val();
                buttonSize = $('.' + location + '-' + buttonType + '-size').val();

                updatePayPalButtonStyling(location, buttonType, buttonShow, buttonLabel, buttonColor, buttonShape, buttonSize);
                customEvent.stopImmediatePropagation();
            });

            $(document).on('change', '.' + location + '-' + buttonType + '-size', function (customEvent) {
                buttonSize = $(this).val();
                buttonShow = $('.' + location + '-' + buttonType + '-show').val();
                buttonLabel = $('.' + location + '-' + buttonType + '-label').val();
                buttonColor = $('.' + location + '-' + buttonType + '-color').val();
                buttonShape = $('.' + location + '-' + buttonType + '-shape').val();

                updatePayPalButtonStyling(location, buttonType, buttonShow, buttonLabel, buttonColor, buttonShape, buttonSize);
                customEvent.stopImmediatePropagation();
            });

            $(document).on('change', '.' + location + '-messaging-show', function (customEvent) {
                messagingShow = $(this).val();
                messagingLayout = $('.' + location + '-messaging-layout').val();
                messagingLogo = $('.' + location + '-messaging-logo').val();
                messagingLogoPosition = $('.' + location + '-messaging-logo-position').val();
                messagingTextColor = $('.' + location + '-messaging-text-color').val();

                renderPayLaterMessages(location, messagingShow, messagingLayout, messagingLogo, messagingLogoPosition, messagingTextColor);
                customEvent.stopImmediatePropagation();
            });

            $(document).on('change', '.' + location + '-messaging-layout', function (customEvent) {
                messagingShow = $('.' + location + '-messaging-show').val();
                messagingLayout = $(this).val();
                messagingLogo = $('.' + location + '-messaging-logo').val();
                messagingLogoPosition = $('.' + location + '-messaging-logo-position').val();
                messagingTextColor = $('.' + location + '-messaging-text-color').val();

                renderPayLaterMessages(location, messagingShow, messagingLayout, messagingLogo, messagingLogoPosition, messagingTextColor);
                customEvent.stopImmediatePropagation();
            });

            $(document).on('change', '.' + location + '-messaging-logo', function (customEvent) {
                messagingShow = $('.' + location + '-messaging-show').val();
                messagingLayout = $('.' + location + '-messaging-layout').val();
                messagingLogo = $(this).val();
                messagingLogoPosition = $('.' + location + '-messaging-logo-position').val();
                messagingTextColor = $('.' + location + '-messaging-text-color').val();

                renderPayLaterMessages(location, messagingShow, messagingLayout, messagingLogo, messagingLogoPosition, messagingTextColor);
                customEvent.stopImmediatePropagation();
            });

            $(document).on('change', '.' + location + '-messaging-logo-position', function (customEvent) {
                messagingShow = $('.' + location + '-messaging-show').val();
                messagingLayout = $('.' + location + '-messaging-layout').val();
                messagingLogo = $('.' + location + '-messaging-logo').val();
                messagingLogoPosition = $(this).val();
                messagingTextColor = $('.' + location + '-messaging-text-color').val();

                renderPayLaterMessages(location, messagingShow, messagingLayout, messagingLogo, messagingLogoPosition, messagingTextColor);
                customEvent.stopImmediatePropagation();
            });

            $(document).on('change', '.' + location + '-messaging-text-color', function (customEvent) {
                messagingShow = $('.' + location + '-messaging-show').val();
                messagingLayout = $('.' + location + '-messaging-layout').val();
                messagingLogo = $('.' + location + '-messaging-logo').val();
                messagingLogoPosition = $('.' + location + '-messaging-logo-position').val();
                messagingTextColor = $(this).val();

                renderPayLaterMessages(location, messagingShow, messagingLayout, messagingLogo, messagingLogoPosition, messagingTextColor);
                customEvent.stopImmediatePropagation();
            });
        });
    });

    /**
     * Update PayPal, Credit and Pay Later button styling if applicable
     * @param location
     * @param buttonType
     * @param buttonShow
     * @param buttonLabel
     * @param buttonColor
     * @param buttonShape
     * @param buttonSize
     */
    let updatePayPalButtonStyling = function (location, buttonType, buttonShow, buttonLabel, buttonColor, buttonShape, buttonSize) {
        $('.action-braintree-paypal-logo').each(function () {
            if ($.inArray($(this).attr('id'), buttonIds) === -1) {
                buttonIds.push($(this).attr('id'));
            }
        });

        buttonIds.each(function (id) {
            let result = id.startsWith(buttonType);
            if (result === true) {
                currentButtonId = id;
            }
        });

        let currentButtonElement = $('#' + currentButtonId);
        if (currentButtonElement.length) {
            let style = {
                color: buttonColor,
                shape: buttonShape,
                size: buttonSize,
                label: buttonLabel
            };
            style.fundingicons = true;
            let fundingSource = buttonType;

            // Render
            let button = paypal.Buttons({
                fundingSource: fundingSource,
                style: style,

                onInit: function (data, actions) {
                    actions.disable();
                }
            });
            if (!button.isEligible()) {
                console.log('PayPal button is not eligible');
                currentButtonElement.parent().remove();
                return;
            }
            if (currentButtonElement.length) {
                currentButtonElement.empty();
                if (buttonShow === '1') {
                    button.render('#' + currentButtonElement.attr('id'));
                }
            }
        }
    };

    /**
     * Render and update Pay Later messaging style
     * @param location
     * @param messagingShow
     * @param messagingLayout
     * @param messagingLogo
     * @param messagingLogoPosition
     * @param messagingTextColor
     */
    let renderPayLaterMessages = function (location, messagingShow, messagingLayout, messagingLogo, messagingLogoPosition, messagingTextColor) {
        $('.action-braintree-paypal-message').each(function () {
            let messageElement = $('#' + $(this).attr('id'));

            let payLaterMessageStyle = {
                layout: messagingLayout,
                text: {
                    color: messagingTextColor
                },
                logo: {
                    type: messagingLogo,
                    position: messagingLogoPosition
                }
            };

            let messageElementId = $(messageElement).attr('id');
            let messageAmount = $(messageElement).data('pp-amount');
            let parentElementId = messageElement.closest('tr').attr('id');

            let messages = paypal.Messages({
                amount: $(messageElement).data('pp-amount'),
                pageType: location,
                style: payLaterMessageStyle
            });

            if (messageElement.length) {
                if (messagingShow === '1') {
                    messageElement.remove();
                    $('#' + parentElementId + ' td.value').append('<div class="action-braintree-paypal-message" id="' + messageElementId + '" data-pp-amount="' + messageAmount + '" data-pp-type="' + location + '" data-messaging-show="' + messagingShow + '" data-messaging-layout="' + messagingLayout + '" data-messaging-logo="' + messagingLogo + '" data-messaging-logo-position="' + messagingLogoPosition + '" data-messaging-text-color="' + messagingTextColor + '"></div>');
                    messages.render('#' + messageElementId);
                } else {
                    messageElement.hide();
                }
            }
        });
    };
});
