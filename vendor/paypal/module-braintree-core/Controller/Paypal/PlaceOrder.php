<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Controller\Paypal;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use PayPal\Braintree\Gateway\Config\PayPal\Config;
use PayPal\Braintree\Model\Paypal\Helper;

class PlaceOrder extends AbstractAction implements HttpPostActionInterface
{
    /**
     * @var Helper\OrderPlace
     */
    private Helper\OrderPlace $orderPlace;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param Session $checkoutSession
     * @param Helper\OrderPlace $orderPlace
     */
    public function __construct(
        Context $context,
        Config $config,
        Session $checkoutSession,
        Helper\OrderPlace $orderPlace
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

        try {
            $quote = $this->checkoutSession->getQuote();
            $this->validateQuote($quote);
            $this->orderPlace->execute($quote, $agreement);

            /** @var Redirect $resultRedirect */
            return $resultRedirect->setPath('checkout/onepage/success', ['_secure' => true]);
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }

        return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
    }
}
