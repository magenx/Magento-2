<?php
/**
 *   _____                    _____
 *  / ____|                  / ____|
 * | |  __  ___ _ __   ___  | |     ___  _ __ ___  _ __ ___   ___ _ __ ___ ___
 * | | |_ |/ _ \ '_ \ / _ \ | |    / _ \| '_ ` _ \| '_ ` _ \ / _ \ '__/ __/ _ \
 * | |__| |  __/ | | |  __/ | |___| (_) | | | | | | | | | | |  __/ | | (_|  __/
 *  \_____|\___|_| |_|\___|  \_____\___/|_| |_| |_|_| |_| |_|\___|_|  \___\___|
 *
 * User: paulcanning
 * Date: 2019-05-02
 * Time: 13:05
 */

namespace PayPal\Braintree\Plugin;

use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Psr\Log\LoggerInterface;

class DeleteStoredPaymentPlugin
{
    /**
     * @var BraintreeAdapter
     */
    private $braintreeAdapter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DeleteStoredPaymentPlugin constructor
     *
     * @param BraintreeAdapter $braintreeAdapter
     * @param LoggerInterface $logger
     */
    public function __construct(
        BraintreeAdapter $braintreeAdapter,
        LoggerInterface $logger
    ) {
        $this->braintreeAdapter = $braintreeAdapter;
        $this->logger = $logger;
    }

    /**
     * @param PaymentTokenRepositoryInterface $subject
     * @param PaymentTokenInterface $paymentToken
     * @return bool|null
     */
    public function beforeDelete(PaymentTokenRepositoryInterface $subject, PaymentTokenInterface $paymentToken)
    {
        try {
            $token = $paymentToken->getGatewayToken();
            $this->braintreeAdapter->deletePaymentMethod($token);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }
}
