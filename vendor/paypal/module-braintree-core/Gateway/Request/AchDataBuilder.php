<?php
declare(strict_types=1);

namespace PayPal\Braintree\Gateway\Request;

use Braintree\Customer;
use Braintree\CustomerSearch;
use Braintree\PaymentMethod;
use Braintree\Result\UsBankAccountVerification;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use PayPal\Braintree\Observer\DataAssignObserver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;

class AchDataBuilder implements BuilderInterface
{
    const OPTIONS = 'options';
    const VERIFICATION_METHOD = 'usBankAccountVerificationMethod';
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * AchDataBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $nonce = $payment->getAdditionalInformation(
            DataAssignObserver::PAYMENT_METHOD_NONCE
        );

        // Get customer details from the billing address
        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();

        // lets search for an existing customer
        $customers = Customer::search([
            CustomerSearch::email()->is($billingAddress->getEmail()),
            CustomerSearch::firstName()->is($billingAddress->getFirstname()),
            CustomerSearch::lastName()->is($billingAddress->getLastname())
        ]);

        if (empty($customers->getIds())) {
            // create customer and get ID
            $result = Customer::create([
                'email' => $billingAddress->getEmail(),
                'firstName' => $billingAddress->getFirstname(),
                'lastName' => $billingAddress->getLastname()
            ]);
            $customerId = $result->customer->id;
        } else {
            $customerId = $customers->getIds()[0];
        }

        $result = PaymentMethod::create([
            'customerId' => $customerId,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'usBankAccountVerificationMethod' => 'network_check'
            ]
        ]);

        if ($result->success) {
            return [
                'paymentMethodNonce' => null,
                'paymentMethodToken' => $result->paymentMethod->token
            ];
        }

        throw new LocalizedException(__('Failed to create payment token.'));
    }
}
