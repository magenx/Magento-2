<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Command;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use Psr\Log\LoggerInterface;
use Magento\Payment\Gateway\Command\CommandException;

/** @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class GatewayCommand implements CommandInterface
{
    /**
     * @var BuilderInterface
     */
    private $requestBuilder;

    /**
     * @var TransferFactoryInterface
     */
    private $transferFactory;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var HandlerInterface
     */
    private $handler;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubjectReader
     */
    private SubjectReader $subjectReader;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @param BuilderInterface $requestBuilder
     * @param TransferFactoryInterface $transferFactory
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param SubjectReader $subjectReader
     * @param OrderRepositoryInterface $orderRepository
     * @param HandlerInterface|null $handler
     * @param ValidatorInterface|null $validator
     */
    public function __construct(
        BuilderInterface $requestBuilder,
        TransferFactoryInterface $transferFactory,
        ClientInterface $client,
        LoggerInterface $logger,
        SubjectReader $subjectReader,
        OrderRepositoryInterface $orderRepository,
        HandlerInterface $handler = null,
        ValidatorInterface $validator = null
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->transferFactory = $transferFactory;
        $this->client = $client;
        $this->handler = $handler;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->subjectReader = $subjectReader;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return void
     * @throws ClientException
     * @throws CommandException
     * @throws ConverterException
     * @throws LocalizedException
     */
    public function execute(array $commandSubject): void
    {
        // @TODO implement exceptions catching
        $transferO = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject)
        );

        $response = $this->client->placeRequest($transferO);
        if (null !== $this->validator) {
            $result = $this->validator->validate(
                array_merge($commandSubject, ['response' => $response])
            );

            if (!$result->isValid()) {
                // TODO attempt to cancel Braintree Transaction
                $this->logExceptions($result->getFailsDescription());
                if ($response['object']->message === 'Transaction can only be voided if status is authorized, submitted_for_settlement, or - for PayPal - settlement_pending.') {
                    $paymentDO = $this->subjectReader->readPayment($commandSubject);
                    $order = $this->orderRepository->get($paymentDO->getOrder()->getId());

                    $order->setState(Order::STATE_CANCELED);
                    $order->setStatus(Order::STATE_CANCELED);

                    $this->orderRepository->save($order);

                    throw new CommandException(__("Order has been cancelled but Braintree Transaction hasn't been voided as Authorization has expired for this transaction."));
                }
                throw new CommandException($this->getExceptionMessage($response));
            }
        }

        if ($this->handler) {
            $this->handler->handle(
                $commandSubject,
                $response
            );
        }
    }

    /**
     * @param $response
     * @return Phrase
     */
    private function getExceptionMessage($response): Phrase
    {
        if (!isset($response['object']) || empty($response['object']->message)) {
            return __('Your payment could not be taken. Please try again or use a different payment method.');
        }

        $allowedMessages = [];

        if (in_array($response['object']->message, $allowedMessages)) {
            return __('Your payment could not be taken. Please try again or use a different payment method.');
        }

        return __(
            'Your payment could not be taken. Please try again or use a different payment method. %1',
            $response['object']->message
        );
    }

    /**
     * @param Phrase[] $fails
     * @return void
     */
    private function logExceptions(array $fails): void
    {
        foreach ($fails as $failPhrase) {
            if (is_array($failPhrase)) {
                foreach ($failPhrase as $phrase) {
                    $this->logger->critical($phrase->getText());
                }
            } else {
                $this->logger->critical($failPhrase->getText());
            }
        }
    }
}
