<?php
declare(strict_types=1);

namespace PayPal\Braintree\Model\Ui\Vault;

use PayPal\Braintree\Gateway\Config\Vault\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'braintree_cc_vault';
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
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::CODE => [
                    'cvvVerify' => $this->config->isCvvVerifyEnabled()
                ]
            ]
        ];
    }
}
