<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Braintree\Controller\Webhook;

use Braintree\TransactionReview;
use Braintree\WebhookNotification;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentSearchResultInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Model\Order;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use PayPal\Braintree\Model\Webhook\Config;
use Psr\Log\LoggerInterface;

class FraudProtection extends Action implements CsrfAwareActionInterface
{
    private const TRANSACTION_DECISION_APPROVED = 'Approve';
    private const TRANSACTION_SETTLED = 'Settled';

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Http
     */
    private Http $httpRequest;

    /**
     * @var Config
     */
    private Config $webhookConfig;

    /**
     * @var BraintreeAdapter
     */
    private BraintreeAdapter $braintreeAdapter;

    /**
     * @var TransactionRepositoryInterface
     */
    private TransactionRepositoryInterface $transactionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private OrderPaymentRepositoryInterface $orderPaymentRepository;

    /**
     * @var OrderManagementInterface
     */
    private OrderManagementInterface $orderManagement;

    /**
     * FraudProtection constructor.
     *
     * @param Context $context
     * @param Config $webhookConfig
     * @param LoggerInterface $logger
     * @param Http $httpRequest
     * @param BraintreeAdapter $braintreeAdapter
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Config $webhookConfig,
        LoggerInterface $logger,
        Http $httpRequest,
        BraintreeAdapter $braintreeAdapter,
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        OrderManagementInterface $orderManagement
    ) {
        parent::__construct($context);
        $this->webhookConfig = $webhookConfig;
        $this->logger = $logger;
        $this->httpRequest = $httpRequest;
        $this->braintreeAdapter = $braintreeAdapter;
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->orderManagement = $orderManagement;
    }

    /**
     * Process braintree webhook response
     *
     * @return ResultInterface|null
     */
    public function execute(): ?ResultInterface
    {
        if ($this->webhookConfig->isEnabled()) {
            if (!empty($webhookBody = $this->httpRequest->getPost())) {
                try {
                    $webhookResponse = WebhookNotification::parse($webhookBody['bt_signature'], $webhookBody['bt_payload']);

                    if (!empty($webhookResponse)) {
                        // Process FPA webhook
                        if ($webhookResponse->kind === WebhookNotification::TRANSACTION_REVIEWED) {
                            $this->processTransactionReviewed($webhookResponse);
                        }

                        // Process ACH payments
                        if (in_array(
                            $webhookResponse->kind,
                            [
                                WebhookNotification::TRANSACTION_SETTLED,
                                WebhookNotification::TRANSACTION_SETTLEMENT_DECLINED
                            ]
                        )) {
                            $this->processSettlement($webhookResponse);
                        }

                        // Process Local Payments
                        if ($webhookResponse->kind === WebhookNotification::LOCAL_PAYMENT_COMPLETED) {
                            $this->processLocalPaymentCompleted($webhookResponse);
                        }
                        if (in_array(
                            $webhookResponse->kind,
                            [
                                WebhookNotification::LOCAL_PAYMENT_EXPIRED,
                                WebhookNotification::LOCAL_PAYMENT_REVERSED
                            ]
                        )) {
                            $this->processLocalPaymentExpiredAndReversed($webhookResponse);
                        }
                    }
                } catch (\Exception $exception) {
                    $this->logger->info("Braintree Webhook ERROR:", [
                        $exception->getMessage()
                    ]);
                }
                return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            }
        }

        return null;
    }

    /**
     * Process the 'transaction_reviewed' webhook kind
     *
     * @param WebhookNotification $webhookResponse
     */
    private function processTransactionReviewed(WebhookNotification $webhookResponse)
    {
        $transactionReview = $webhookResponse->transactionReview;
        $transactionData = $this->getOrderByTransaction($transactionReview->transactionId);
        if ($transactionData->getTotalCount() > 0) {
            foreach ($transactionData->getItems() as $transaction) {
                $order = $this->orderRepository->get($transaction->getOrderId());
                if ($transactionReview->decision === self::TRANSACTION_DECISION_APPROVED) {
                    $this->approveOrder($order, $transactionReview);
                } else {
                    $this->rejectOrder($order, $transactionReview);
                }
            }
        }
    }

    /**
     * Process the settlement webhook kind
     *
     * @param WebhookNotification $webhookResponse
     */
    private function processSettlement(WebhookNotification $webhookResponse)
    {
        $transactionReview = $webhookResponse->transaction;
        $transactionData = $this->getOrderByTransaction($transactionReview->id);
        if ($transactionData->getTotalCount() > 0) {
            foreach ($transactionData->getItems() as $transaction) {
                $order = $this->orderRepository->get($transaction->getOrderId());
                if ($transactionReview->status === self::TRANSACTION_SETTLED) {
                    $this->approveOrder($order, $transactionReview);
                } else {
                    $this->rejectOrder($order, $transactionReview);
                }
            }
        }
    }

    /**
     * Get Order By Transaction
     *
     * @param string $transactionId
     * @return TransactionSearchResultInterface
     */
    private function getOrderByTransaction(string $transactionId): TransactionSearchResultInterface
    {
        $this->searchCriteriaBuilder->addFilter('txn_id', $transactionId);
        return $this->transactionRepository->getList(
            $this->searchCriteriaBuilder->create()
        );
    }

    /**
     * Approve Order
     *
     * @param OrderInterface $order
     * @param TransactionReview $transactionReview
     */
    private function approveOrder(OrderInterface $order, TransactionReview $transactionReview)
    {
        $approvedStatus = $this->webhookConfig->getFraudApproveOrderStatus();
        $order->setState($approvedStatus)
            ->setStatus($approvedStatus)
            ->addCommentToStatusHistory(__('Payment approved for Transaction ID: "%1". %2.', $transactionReview->transactionId, $transactionReview->reviewerNote));
        $this->orderRepository->save($order);
    }

    /**
     * Reject Order
     *
     * @param OrderInterface $order
     * @param TransactionReview $transactionReview
     */
    private function rejectOrder(OrderInterface $order, TransactionReview $transactionReview)
    {
        $rejectedStatus = $this->webhookConfig->getFraudRejectOrderStatus();
        $order->setState($rejectedStatus)
            ->setStatus($rejectedStatus)
            ->addCommentToStatusHistory(__('Payment declined for Transaction ID: "%1". %2.', $transactionReview->transactionId, $transactionReview->reviewerNote));
        $this->orderRepository->save($order);
    }

    /**
     * Process the 'local_payment_completed' webhook kind
     *
     * @param WebhookNotification $webhookResponse
     */
    private function processLocalPaymentCompleted(WebhookNotification $webhookResponse)
    {
        $paymentId = $webhookResponse->localPaymentCompleted->paymentId;

        $orderPayment = $this->getOrderByPaymentId($paymentId);
        if ($orderPayment->getTotalCount() > 0) {
            foreach ($orderPayment->getItems() as $transaction) {
                $order = $this->orderRepository->get($transaction->getParentId());

                if ($order->getStatus() !== Order::STATE_PROCESSING) {
                    $order->setState(Order::STATE_PROCESSING)
                        ->setStatus(Order::STATE_PROCESSING)
                        ->addCommentToStatusHistory(__('Local Payment approved for Transaction ID: "%1"', $transaction->getLastTransId()));
                    $this->orderRepository->save($order);
                }
            }
        }
    }

    /**
     * Process the 'local_payment_expired'
     * And 'local_payment_reversed' webhook kind
     *
     * @param WebhookNotification $webhookResponse
     */
    private function processLocalPaymentExpiredAndReversed(WebhookNotification $webhookResponse)
    {
        if ($webhookResponse->kind === WebhookNotification::LOCAL_PAYMENT_EXPIRED) {
            $payPalPaymentId = $webhookResponse->localPaymentExpired->paymentId;
            $statusComment = 'Payment expired for Transaction ID:';
        } else {
            $payPalPaymentId = $webhookResponse->localPaymentReversed->paymentId;
            $statusComment = 'Payment reversed for Transaction ID:';
        }

        $paymentTransaction = $this->getOrderByPaymentId($payPalPaymentId);
        if ($paymentTransaction->getTotalCount() > 0) {
            foreach ($paymentTransaction->getItems() as $transaction) {
                $order = $this->orderRepository->get($transaction->getParentId());
                if ($order->getStatus() !== Order::STATE_CANCELED) {
                    $this->orderManagement->cancel($order->getId());
                }
                $order->addCommentToStatusHistory(__($statusComment . ' "%1"', $transaction->getLastTransId()));
                $this->orderRepository->save($order);
            }
        }
    }

    /**
     * Get order by PayPal paymentId
     *
     * @param string $paymentId
     * @return OrderPaymentSearchResultInterface
     */
    private function getOrderByPaymentId(string $paymentId): OrderPaymentSearchResultInterface
    {
        $this->searchCriteriaBuilder->addFilter('additional_information', "%$paymentId%", 'like');
        return $this->orderPaymentRepository->getList(
            $this->searchCriteriaBuilder->create()
        );
    }

    /**
     * @inheritdoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
