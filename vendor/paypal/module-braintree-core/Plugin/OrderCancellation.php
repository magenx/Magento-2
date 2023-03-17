<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace PayPal\Braintree\Plugin;

use Closure;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use PayPal\Braintree\Model\Paypal\OrderCancellationService;
use PayPal\Braintree\Model\Ui\ConfigProvider;
use PayPal\Braintree\Model\Ui\PayPal\ConfigProvider as PayPalConfigProvider;

/**
 * Cancels an order and an authorization transaction.
 */
class OrderCancellation
{
    /**
     * @var OrderCancellationService
     */
    private OrderCancellationService $orderCancellationService;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @param OrderCancellationService $orderCancellationService
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        OrderCancellationService $orderCancellationService,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->orderCancellationService = $orderCancellationService;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Cancels an order if an exception occurs during the order creation.
     *
     * @param CartManagementInterface $subject
     * @param Closure $proceed
     * @param int $cartId
     * @param PaymentInterface|null $payment
     * @return int
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPlaceOrder(
        CartManagementInterface $subject,
        Closure $proceed,
        int $cartId,
        PaymentInterface $payment = null
    ): int {
        try {
            return (int)$proceed($cartId, $payment);
        } catch (\Exception $e) {
            $quote = $this->quoteRepository->get((int) $cartId);
            $payment = $quote->getPayment();
            $paymentCodes = [
                ConfigProvider::CODE,
                ConfigProvider::CC_VAULT_CODE,
                PayPalConfigProvider::PAYPAL_CODE,
                PayPalConfigProvider::PAYPAL_VAULT_CODE
            ];
            if (in_array($payment->getMethod(), $paymentCodes)) {
                $incrementId = $quote->getReservedOrderId();
                if ($incrementId) {
                    $this->orderCancellationService->execute($incrementId);
                }
            }

            throw $e;
        }
    }
}
