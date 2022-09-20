<?php

namespace PayPal\Braintree\Block\Credit\Calculator\Product;

use PayPal\Braintree\Api\CreditPriceRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use PayPal\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;

/**
 * @api
 * @since 100.0.2
 */
class View extends Template
{
    /**
     * @var CreditPriceRepositoryInterface
     */
    protected $creditPriceRepository;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var PayPalCreditConfig
     */
    protected $config;

    /**
     * View constructor.
     * @param Template\Context $context
     * @param PayPalCreditConfig $config
     * @param Registry $registry
     * @param CreditPriceRepositoryInterface $creditPriceRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PayPalCreditConfig $config,
        Registry $registry,
        CreditPriceRepositoryInterface $creditPriceRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->creditPriceRepository = $creditPriceRepository;
        $this->coreRegistry = $registry;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml(): string
    {
        if ($this->config->isCalculatorEnabled()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * Retrieve current product model
     *
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * @return string|bool
     */
    public function getPriceData()
    {
        if ($this->getProduct()) {
            $results = $this->creditPriceRepository->getByProductId($this->getProduct()->getId());
            if (null !== $results) {
                $options = [];
                foreach ($results as $option) {
                    $options[] = [
                        'term' => $option->getTerm(),
                        'monthlyPayment' => $option->getMonthlyPayment(),
                        'apr' => $option->getInstalmentRate(),
                        'cost' => $option->getCostOfPurchase(),
                        'costIncInterest' => $option->getTotalIncInterest()
                    ];
                }

                return json_encode($options);
            }
        }

        return false;
    }

    /**
     * @return string|null
     */
    public function getMerchantName()
    {
        return $this->config->getMerchantName();
    }
}
