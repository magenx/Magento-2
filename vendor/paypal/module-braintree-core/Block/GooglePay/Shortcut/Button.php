<?php

namespace PayPal\Braintree\Block\GooglePay\Shortcut;

use PayPal\Braintree\Block\GooglePay\AbstractButton;
use PayPal\Braintree\Model\GooglePay\Auth;
use Magento\Checkout\Model\Session;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\MethodInterface;

class Button extends AbstractButton implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    const BUTTON_ELEMENT_INDEX = 'button_id';

    /**
     * @var DefaultConfigProvider $defaultConfigProvider
     */
    private $defaultConfigProvider;

    /**
     * Button constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param MethodInterface $payment
     * @param Auth $auth
     * @param DefaultConfigProvider $defaultConfigProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        MethodInterface $payment,
        Auth $auth,
        DefaultConfigProvider $defaultConfigProvider,
        array $data = []
    ) {
        parent::__construct($context, $checkoutSession, $payment, $auth, $data);
        $this->defaultConfigProvider = $defaultConfigProvider;
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getContainerId(): string
    {
        return $this->getData(self::BUTTON_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getExtraClassname(): string
    {
        return $this->getIsCart() ? 'cart' : 'minicart';
    }
}
