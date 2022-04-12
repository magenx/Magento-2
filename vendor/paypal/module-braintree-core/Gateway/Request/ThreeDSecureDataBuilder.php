<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Request;

use PayPal\Braintree\Gateway\Config\Config;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use PayPal\Braintree\Model\Ui\ConfigProvider;

class ThreeDSecureDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * Constructor
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(Config $config, SubjectReader $subjectReader)
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $result = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $amount = $this->formatPrice($this->subjectReader->readAmount($buildSubject));

        // disable 3d secure for vault CC payment method
        if ($paymentDO->getPayment()->getMethod() == ConfigProvider::CC_VAULT_CODE && $paymentDO->getOrder()->isMultishipping()) {
            return $result;
        }
        if ($this->is3DSecureEnabled($paymentDO->getOrder(), $amount)) {
            $result['options']['threeDSecure'] = ['required' => true]; // 'three_d_secure' was removed in version 4.x.x
        }
        return $result;
    }

    /**
     * Check if 3d secure is enabled
     * @param OrderAdapterInterface $order
     * @param float $amount
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function is3DSecureEnabled(OrderAdapterInterface $order, $amount): bool
    {
        if (!$this->config->isVerify3DSecure() || $amount < $this->config->getThresholdAmount()) {
            return false;
        }

        $billingAddress = $order->getBillingAddress();
        $specificCounties = $this->config->get3DSecureSpecificCountries();

        return !(!empty($specificCounties) && !in_array($billingAddress->getCountryId(), $specificCounties));
    }
}
