<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Command;

use Magento\Framework\Phrase;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use PayPal\Braintree\Model\Recaptcha\ReCaptchaValidation;
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
     * @var ReCaptchaValidation
     */
    private $reCaptchaValidation;

    /**
     * @var IsCaptchaEnabledInterface
     */
    private $isCaptchaEnabled;

    /**
     * @param BuilderInterface $requestBuilder
     * @param TransferFactoryInterface $transferFactory
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param HandlerInterface|null $handler
     * @param ValidatorInterface|null $validator
     * @param ReCaptchaValidation $reCaptchaValidation
     * @param IsCaptchaEnabledInterface $isCaptchaEnabled
     */
    public function __construct(
        BuilderInterface $requestBuilder,
        TransferFactoryInterface $transferFactory,
        ClientInterface $client,
        LoggerInterface $logger,
        HandlerInterface $handler = null,
        ValidatorInterface $validator = null,
        ReCaptchaValidation $reCaptchaValidation,
        IsCaptchaEnabledInterface $isCaptchaEnabled
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->transferFactory = $transferFactory;
        $this->client = $client;
        $this->handler = $handler;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->reCaptchaValidation = $reCaptchaValidation;
        $this->isCaptchaEnabled = $isCaptchaEnabled;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return void
     * @throws CommandException
     * @throws ClientException
     * @throws ConverterException
     */
    public function execute(array $commandSubject)
    {
        // @TODO implement exceptions catching
        $transferO = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject)
        );

        $key = 'braintree';
        if ($this->isCaptchaEnabled->isCaptchaEnabledFor($key) && isset($transferO->getBody()['paymentMethodNonce'])) {
            $this->reCaptchaValidation->validate($commandSubject);
        }

        $response = $this->client->placeRequest($transferO);
        if (null !== $this->validator) {
            $result = $this->validator->validate(
                array_merge($commandSubject, ['response' => $response])
            );
            if (!$result->isValid()) {
                // TODO attempt to cancel Braintree Transaction
                $this->logExceptions($result->getFailsDescription());
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
    private function logExceptions(array $fails)
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
