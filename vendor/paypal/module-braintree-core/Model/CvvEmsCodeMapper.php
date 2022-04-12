<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model;

use InvalidArgumentException;
use PayPal\Braintree\Gateway\Response\PaymentDetailsHandler;
use PayPal\Braintree\Model\Ui\ConfigProvider;
use Magento\Payment\Api\PaymentVerificationInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Processes CVV codes mapping from Braintree transaction to
 * electronic merchant systems standard.
 *
 * @see https://developers.braintreepayments.com/reference/response/transaction
 * @see http://www.emsecommerce.net/avs_cvv2_response_codes.htm
 */
class CvvEmsCodeMapper implements PaymentVerificationInterface
{
    /**
     * Default code for mismatch mapping
     *
     * @var string
     */
    private static $notProvidedCode = 'P';

    /**
     * List of mapping CVV codes
     *
     * @var array
     */
    private static $cvvMap = [
        'M' => 'M',
        'N' => 'N',
        'U' => 'P',
        'I' => 'P',
        'S' => 'S',
        'A' => ''
    ];

    /**
     * @inheritDoc
     */
    public function getCode(OrderPaymentInterface $orderPayment): string
    {
        if ($orderPayment->getMethod() !== ConfigProvider::CODE) {
            throw new InvalidArgumentException(
                'The "' . $orderPayment->getMethod() . '" does not supported by Braintree CVV mapper.'
            );
        }

        $additionalInfo = $orderPayment->getAdditionalInformation();
        if (empty($additionalInfo[PaymentDetailsHandler::CVV_RESPONSE_CODE])) {
            return self::$notProvidedCode;
        }

        $cvv = $additionalInfo[PaymentDetailsHandler::CVV_RESPONSE_CODE];
        return self::$cvvMap[$cvv] ?? self::$notProvidedCode;
    }
}
