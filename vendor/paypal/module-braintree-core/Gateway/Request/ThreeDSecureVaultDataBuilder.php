<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Request;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use PayPal\Braintree\Gateway\Config\Config;
use PayPal\Braintree\Gateway\Helper\SubjectReader;

class ThreeDSecureVaultDataBuilder extends ThreeDSecureDataBuilder
{

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * ThreeDSecureVaultDataBuilder constructor.
     * @param RequestInterface $request
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        RequestInterface $request,
        Config $config,
        SubjectReader $subjectReader
    ) {
        parent::__construct($config, $subjectReader);
        $this->request = $request;
    }

    /**
     * Check if 3d secure is enabled
     *
     * @param OrderAdapterInterface $order
     * @param float $amount
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function is3DSecureEnabled(OrderAdapterInterface $order, $amount): bool
    {
        if ($this->request->isSecure() && $this->config->isCvvEnabledVault()) {
            return false;
        }

        return parent::is3DSecureEnabled($order, $amount);
    }
}
