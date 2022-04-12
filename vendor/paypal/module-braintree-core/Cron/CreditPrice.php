<?php

namespace PayPal\Braintree\Cron;

use PayPal\Braintree\Api\Data\CreditPriceDataInterface;
use PayPal\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class CreditPrice
{
    /**
     * @var \PayPal\Braintree\Api\CreditPriceRepositoryInterface
     */
    private $creditPriceRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollection;

    /**
     * @var \PayPal\Braintree\Api\Data\CreditPriceDataInterfaceFactory
     */
    private $creditPriceFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \PayPal\Braintree\Model\Paypal\CreditApi
     */
    private $creditApi;

    /**
     * @var PayPalCreditConfig
     */
    private $config;

    /**
     * CreditPrice constructor.
     * @param \PayPal\Braintree\Api\CreditPriceRepositoryInterface $creditPriceRepository
     * @param \PayPal\Braintree\Api\Data\CreditPriceDataInterfaceFactory $creditPriceDataInterfaceFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \PayPal\Braintree\Model\Paypal\CreditApi $creditApi
     * @param ProductCollectionFactory $productCollection
     * @param LoggerInterface $logger
     * @param PayPalCreditConfig $config
     */
    public function __construct(
        \PayPal\Braintree\Api\CreditPriceRepositoryInterface $creditPriceRepository,
        \PayPal\Braintree\Api\Data\CreditPriceDataInterfaceFactory $creditPriceDataInterfaceFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \PayPal\Braintree\Model\Paypal\CreditApi $creditApi,
        ProductCollectionFactory $productCollection,
        LoggerInterface $logger,
        PayPalCreditConfig $config
    ) {
        $this->creditPriceRepository = $creditPriceRepository;
        $this->scopeConfig = $scopeConfig;
        $this->productCollection = $productCollection;
        $this->logger = $logger;
        $this->creditPriceFactory = $creditPriceDataInterfaceFactory;
        $this->creditApi = $creditApi;
        $this->config = $config;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function execute()
    {
        if (!$this->config->isCalculatorEnabled()) {
            return $this;
        }

        // Retrieve paginated collection of product and their price
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollection->create();
        $collection->addAttributeToSelect('price')
            ->setPageSize(100);

        $connection = $collection->getResource()->getConnection();
        $connection->beginTransaction();

        $lastPage = $collection->getLastPageNumber();
        for ($i = 1; $i <= $lastPage; $i++) {
            $collection->setCurPage($i);
            $collection->load();

            foreach ($collection as $product) {
                try {
                    // Delete by product_id
                    $this->creditPriceRepository->deleteByProductId($product->getId());

                    // Retrieve data from PayPal
                    $priceOptions = $this->creditApi->getPriceOptions($product->getFinalPrice());
                    foreach ($priceOptions as $priceOption) {
                        // Populate model
                        /** @var $model \PayPal\Braintree\Api\Data\CreditPriceDataInterface */
                        $model = $this->creditPriceFactory->create();
                        $model->setProductId($product->getId());
                        $model->setTerm($priceOption['term']);
                        $model->setMonthlyPayment($priceOption['monthly_payment']);
                        $model->setInstalmentRate($priceOption['instalment_rate']);
                        $model->setCostOfPurchase($priceOption['cost_of_purchase']);
                        $model->setTotalIncInterest($priceOption['total_inc_interest']);

                        $this->creditPriceRepository->save($model);
                    }
                } catch (AuthenticationException $e) {
                    $connection->rollBack();
                    throw $e;
                } catch (LocalizedException $e) {
                    $this->logger->critical($e->getMessage());
                } catch (\Exception $e) {
                    $connection->rollBack();
                    $this->logger->critical($e->getMessage());
                    return $this;
                }
            }

            $collection->clear();
        }

        $connection->commit();

        return $this;
    }
}
