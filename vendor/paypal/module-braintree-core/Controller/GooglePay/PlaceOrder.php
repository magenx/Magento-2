<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Controller\GooglePay;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use PayPal\Braintree\Model\GooglePay\Config;
use PayPal\Braintree\Model\Paypal\Helper\OrderPlace;

class PlaceOrder extends AbstractAction implements HttpPostActionInterface
{
    /**
     * @var OrderPlace
     */
    private $orderPlace;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param Session $checkoutSession
     * @param OrderPlace $orderPlace
     */
    public function __construct(
        Context $context,
        Config $config,
        Session $checkoutSession,
        OrderPlace $orderPlace
    ) {
        parent::__construct($context, $config, $checkoutSession);

        $this->orderPlace = $orderPlace;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $agreement = array_keys($this->getRequest()->getPostValue('agreement', []));
        $quote = $this->checkoutSession->getQuote();

        try {
            $this->validateQuote($quote);
            $this->orderPlace->execute($quote, $agreement);

            /** @var Redirect $resultRedirect */
            return $resultRedirect->setPath('checkout/onepage/success', ['_secure' => true]);
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
    }
}
