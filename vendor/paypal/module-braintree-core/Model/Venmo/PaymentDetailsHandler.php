<?php
declare(strict_types=1);

namespace PayPal\Braintree\Model\Venmo;

class PaymentDetailsHandler extends \PayPal\Braintree\Gateway\Response\PaymentDetailsHandler
{
    /**
     * List of additional details
     * @var array $additionalInformationMapping
     */
    protected $additionalInformationMapping = [
        self::PROCESSOR_AUTHORIZATION_CODE,
        self::PROCESSOR_RESPONSE_CODE,
        self::PROCESSOR_RESPONSE_TEXT
    ];
}
