<?php

namespace PayPal\Braintree\Gateway\Request;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use PayPal\Braintree\Gateway\Config\Config;
use Psr\Log\LoggerInterface;

class CvvDataBuilder implements BuilderInterface
{
    /**
     * @var RequestInterface $request
     */
    private $request;

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * CvvDataBuilder constructor.
     * @param RequestInterface $request
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        if (!$this->request->isSecure() || !$this->config->isCvvEnabledVault()) {
            return [];
        }

        try {
            $input = file_get_contents('php://input'); // @codingStandardsIgnoreLine
            if ($input) {
                $input = json_decode($input, true);
                if (!empty($input['paymentMethod']['additional_data']['cvv'])) {
                    return [
                        'creditCard' => [
                            'cvv' => $input['paymentMethod']['additional_data']['cvv']
                        ]
                    ];
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return [];
    }
}
