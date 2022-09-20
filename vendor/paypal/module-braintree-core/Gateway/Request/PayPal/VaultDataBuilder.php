<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Request\PayPal;

use PayPal\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use PayPal\Braintree\Gateway\Request\VaultDataBuilder as BraintreeVaultDataBuilder;

class VaultDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader $subjectReader
     */
    private $subjectReader;

    /**
     * VaultDataBuilder constructor
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
        $result = [];
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $data = $payment->getAdditionalInformation();

        if (!empty($data[VaultConfigProvider::IS_ACTIVE_CODE])) {
            $result[BraintreeVaultDataBuilder::OPTIONS] = [
                BraintreeVaultDataBuilder::STORE_IN_VAULT_ON_SUCCESS => true
            ];
        }

        return $result;
    }
}
