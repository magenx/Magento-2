<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Response\Venmo;

use Braintree\Transaction;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use PayPal\Braintree\Gateway\Config\Config;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use PayPal\Braintree\Gateway\Response\Handler;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;
use RuntimeException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultDetailsHandler extends Handler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $transaction = $this->subjectReader->readTransaction($response);
        $payment = $paymentDO->getPayment();

        // add vault payment token entity to extension attributes
        $paymentToken = $this->getVaultPaymentToken($transaction);
        if (null !== $paymentToken) {
            $extensionAttributes = $this->getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }
    }

    /**
     * Get vault payment token entity
     *
     * @param Transaction $transaction
     * @return PaymentTokenInterface|null
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    protected function getVaultPaymentToken(Transaction $transaction)
    {
        // Check token existing in gateway response
        if (!isset($transaction->venmoAccount->token)) {
            return null;
        }

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setGatewayToken($transaction->venmoAccount->token);
        $paymentToken->setExpiresAt($this->getExpirationDate());

        $paymentToken->setTokenDetails($this->convertDetailsToJSON([
            'username' => $transaction->venmoAccount->username,
        ]));

        return $paymentToken;
    }

    /**
     * Get expiration date
     *
     * @return string
     * @throws Exception
     */
    private function getExpirationDate(): string
    {
        $expDate = new DateTime('now', new DateTimeZone('UTC'));
        $expDate->add(new DateInterval('P30D'));
        return $expDate->format('Y-m-d 00:00:00');
    }
}
