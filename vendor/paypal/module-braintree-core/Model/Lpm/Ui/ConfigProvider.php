<?php
declare(strict_types=1);

namespace PayPal\Braintree\Model\Lpm\Ui;

use PayPal\Braintree\Model\Lpm\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'braintree_local_payment';
    /**
     * @var Config
     */
    private $config;

    /**
     * ConfigProvider constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getConfig(): array
    {
        if (!$this->config->isActive()) {
            return [];
        }

        return [
            'payment' => [
                self::METHOD_CODE => [
                    'allowedMethods' => $this->config->getAllowedMethods(),
                    'clientToken' => $this->config->getClientToken(),
                    'merchantAccountId' => $this->config->getMerchantAccountId(),
                    'paymentIcons' => $this->config->getPaymentIcons(),
                    'title' => $this->config->getTitle()
                ]
            ]
        ];
    }
}
