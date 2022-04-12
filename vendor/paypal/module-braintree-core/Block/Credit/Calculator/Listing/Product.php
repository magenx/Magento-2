<?php

namespace PayPal\Braintree\Block\Credit\Calculator\Listing;

use PayPal\Braintree\Api\CreditPriceRepositoryInterface;
use PayPal\Braintree\Api\Data\CreditPriceDataInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\View\Element\Template;
use PayPal\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;

class Product extends Template
{
    /**
     * @var string
     */
    protected $_template = 'PayPal_Braintree::credit/product/listing.phtml'; // @codingStandardsIgnoreLine

    /**
     * @var CreditPriceRepositoryInterface
     */
    protected $creditPriceRepository;

    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var PayPalCreditConfig
     */
    protected $config;

    /**
     * Product constructor.
     * @param Template\Context $context
     * @param PayPalCreditConfig $config
     * @param CreditPriceRepositoryInterface $creditPriceRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PayPalCreditConfig $config,
        CreditPriceRepositoryInterface $creditPriceRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->creditPriceRepository = $creditPriceRepository;
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
     * @param $product
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;
    }

    /**
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    /**
     * @return CreditPriceDataInterface|bool
     */
    public function getPriceData()
    {
        $data = $this->creditPriceRepository->getCheapestByProductId($this->getProduct()->getId());
        if ($data->getId()) {
            return $data;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getMerchantName(): string
    {
        return $this->config->getMerchantName();
    }
}
