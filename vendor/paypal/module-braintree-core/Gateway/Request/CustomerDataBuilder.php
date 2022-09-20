<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use PayPal\Braintree\Gateway\Helper\SubjectReader;

class CustomerDataBuilder implements BuilderInterface
{
    /**
     * Customer block name
     */
    public const CUSTOMER = 'customer';

    /**
     * The first name value must be less than or equal to 255 characters.
     */
    public const FIRST_NAME = 'firstName';

    /**
     * The last name value must be less than or equal to 255 characters.
     */
    public const LAST_NAME = 'lastName';

    /**
     * The customer’s company. 255 character maximum.
     */
    public const COMPANY = 'company';

    /**
     * The customer’s email address, comprised of ASCII characters.
     */
    public const EMAIL = 'email';

    /**
     * Phone number. Phone must be 10-14 characters and can
     * only contain numbers, dashes, parentheses and periods.
     */
    public const PHONE = 'phone';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();

        return [
            self::CUSTOMER => [
                self::FIRST_NAME => $billingAddress->getFirstname(),
                self::LAST_NAME => $billingAddress->getLastname(),
                self::COMPANY => $billingAddress->getCompany(),
                self::PHONE => $billingAddress->getTelephone(),
                self::EMAIL => $billingAddress->getEmail(),
            ]
        ];
    }
}
