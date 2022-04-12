<?php

namespace PayPal\Braintree\Block\GooglePay;

use PayPal\Braintree\Model\GooglePay\Auth;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\MethodInterface;

/***/
abstract class AbstractButton extends Template
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var MethodInterface
     */
    protected $payment;

    /**
     * @var Auth
     */
    protected $auth;

    /**
     * Button constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param MethodInterface $payment
     * @param Auth $auth
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        MethodInterface $payment,
        Auth $auth,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->payment = $payment;
        $this->auth = $auth;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml(): string // @codingStandardsIgnoreLine
    {
        if ($this->isActive()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->payment->isAvailable($this->checkoutSession->getQuote());
    }

    /**
     * Merchant name to display in popup
     *
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->auth->getMerchantId();
    }

    /**
     * Get environment code
     *
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getEnvironment(): string
    {
        return $this->auth->getEnvironment();
    }

    /**
     * Braintree's API token
     *
     * @return string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getClientToken()
    {
        return $this->auth->getClientToken();
    }

    /**
     * URL To success page
     *
     * @return string
     */
    public function getActionSuccess(): string
    {
        return $this->getUrl('braintree/googlepay/review', ['_secure' => true]);
    }

    /**
     * Currency code
     *
     * @return float|null
     */
    public function getCurrencyCode()
    {
        if ($this->checkoutSession->getQuote()->getCurrency()) {
            return $this->checkoutSession->getQuote()->getCurrency()->getBaseCurrencyCode();
        }

        return null;
    }

    /**
     * Cart grand total
     *
     * @return float|null
     */
    public function getAmount()
    {
        return $this->checkoutSession->getQuote()->getBaseGrandTotal();
    }

    /**
     * Available card types
     *
     * @return mixed
     */
    public function getAvailableCardTypes()
    {
        return $this->auth->getAvailableCardTypes();
    }

    /**
     * BTN Color
     *
     * @return mixed
     */
    public function getBtnColor()
    {
        return $this->auth->getBtnColor();
    }
}
