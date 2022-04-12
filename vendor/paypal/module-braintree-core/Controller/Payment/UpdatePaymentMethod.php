<?php
declare(strict_types=1);

namespace PayPal\Braintree\Controller\Payment;

use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;

class UpdatePaymentMethod extends Action
{
    /**
     * @var BraintreeAdapter
     */
    private $adapter;
    /**
     * @var PaymentTokenManagementInterface
     */
    private $tokenManagement;
    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * UpdatePaymentMethod constructor.
     *
     * @param Context $context
     * @param BraintreeAdapter $adapter
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param SessionManagerInterface $session
     */
    public function __construct(
        Context $context,
        BraintreeAdapter $adapter,
        PaymentTokenManagementInterface $tokenManagement,
        SessionManagerInterface $session
    ) {
        parent::__construct($context);
        $this->adapter = $adapter;
        $this->tokenManagement = $tokenManagement;
        $this->session = $session;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $publicHash = $this->getRequest()->getParam('public_hash');
        $nonce = $this->getRequest()->getParam('nonce');

        $customerId = $this->session->getCustomerId();

        $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $customerId);

        $result = $this->adapter->updatePaymentMethod(
            $paymentToken->getGatewayToken(),
            [
                'paymentMethodNonce' => $nonce,
                'options' => [
                    'verifyCard' => true
                ]
            ]
        );

        $response->setData(['success' => (bool) $result->success]);

        return $response;
    }
}
