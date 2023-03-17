<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use PayPal\Braintree\Gateway\Helper\SubjectReader;

class VaultDataBuilder implements BuilderInterface
{
    /**
     * Additional options in request to gateway
     */
    public const OPTIONS = 'options';

    /**
     * The option that determines whether the payment method associated with
     * the successful transaction should be stored in the Vault.
     */
    public const STORE_IN_VAULT_ON_SUCCESS = 'storeInVaultOnSuccess';

    /**
     * @var SubjectReader $subjectReader
     */
    private SubjectReader $subjectReader;

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
            $result[self::OPTIONS] = [
                self::STORE_IN_VAULT_ON_SUCCESS => true
            ];
        }

        return $result;
    }
}
