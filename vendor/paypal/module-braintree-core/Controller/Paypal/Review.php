<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Controller\Paypal;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use PayPal\Braintree\Gateway\Config\PayPal\Config;
use PayPal\Braintree\Model\Paypal\Helper\QuoteUpdater;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Page;

class Review extends AbstractAction
{
    /**
     * @var QuoteUpdater
     */
    private $quoteUpdater;

    /**
     * @var string
     */
    private static $paymentMethodNonce = 'payment_method_nonce';

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param Session $checkoutSession
     * @param QuoteUpdater $quoteUpdater
     */
    public function __construct(
        Context $context,
        Config $config,
        Session $checkoutSession,
        QuoteUpdater $quoteUpdater
    ) {
        parent::__construct($context, $config, $checkoutSession);
        $this->quoteUpdater = $quoteUpdater;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $requestData = json_decode(
            $this->getRequest()->getPostValue('result', '{}'),
            true
        );
        $quote = $this->checkoutSession->getQuote();

        try {
            if (is_array($requestData) === false) {
                throw new LocalizedException(__('Malformed request data. This may be caused by special characters. Please try again'));
            }
            $this->validateQuote($quote);

            if ($this->validateRequestData($requestData)) {
                $this->quoteUpdater->execute(
                    $requestData['nonce'],
                    $requestData['details'],
                    $quote
                );
            } elseif (!$quote->getPayment()->getAdditionalInformation(self::$paymentMethodNonce)) {
                throw new LocalizedException(__('We can\'t initialize checkout.'));
            }

            /** @var Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

            /** @var \PayPal\Braintree\Block\Paypal\Checkout\Review $reviewBlock */
            $reviewBlock = $resultPage->getLayout()->getBlock('braintree.paypal.review');

            $reviewBlock->setQuote($quote);
            $reviewBlock->getChildBlock('shipping_method')->setData('quote', $quote);

            return $resultPage;
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
    }

    /**
     * @param array $requestData
     * @return boolean
     */
    private function validateRequestData(array $requestData): bool
    {
        return !empty($requestData['nonce']) && !empty($requestData['details']);
    }
}
