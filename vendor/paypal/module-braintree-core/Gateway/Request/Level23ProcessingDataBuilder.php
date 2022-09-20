<?php
declare(strict_types=1);

namespace PayPal\Braintree\Gateway\Request;

use Braintree\TransactionLineItem;
use Magento\Directory\Model\Country;
use PayPal\Braintree\Gateway\Data\Order\OrderAdapter;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Level23ProcessingDataBuilder
 * @package PayPal\Braintree\Gateway\Request
 */
class Level23ProcessingDataBuilder implements BuilderInterface
{
    const KEY_PURCHASE_ORDER_NUMBER = 'purchaseOrderNumber';
    const KEY_TAX_AMT = 'taxAmount';
    const KEY_SHIPPING_AMT = 'shippingAmount';
    const KEY_DISCOUNT_AMT = 'discountAmount';
    const KEY_SHIPS_FROM_POSTAL_CODE = 'shipsFromPostalCode';
    const KEY_SHIPPING = 'shipping';
    const KEY_COUNTRY_CODE_ALPHA_3 = 'countryCodeAlpha3';
    const KEY_LINE_ITEMS = 'lineItems';
    const LINE_ITEMS_ARRAY = [
        'name',
        'kind',
        'quantity',
        'unitAmount',
        'unitOfMeasure',
        'totalAmount',
        'taxAmount',
        'discountAmount',
        'productCode',
        'commodityCode'
    ];

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Country
     */
    private $country;

    /**
     * Level23ProcessingDataBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     * @param ScopeConfigInterface $scopeConfig
     * @param Country $country
     */
    public function __construct(
        SubjectReader $subjectReader,
        ScopeConfigInterface $scopeConfig,
        Country $country
    ) {
        $this->subjectReader = $subjectReader;
        $this->scopeConfig = $scopeConfig;
        $this->country = $country;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $lineItems = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();

        /**
         * Override in di.xml so we can add extra public methods.
         * In this instance, so we can eventually get the discount amount.
         * @var OrderAdapter $order
         */
        $order = $paymentDO->getOrder();

        foreach ($order->getItems() as $item) {

            // Skip configurable parent items and items with a base price of 0.
            if ($item->getParentItem() || 0.0 === $item->getPrice()) {
                continue;
            }

            // Regex to replace all unsupported characters.
            $filteredFields = preg_replace(
                '/[^a-zA-Z0-9\s\-.\']/',
                '',
                [
                    'name' => substr($item->getName(), 0, 35),
                    'unit_of_measure' => substr($item->getProductType(), 0, 12),
                    'sku' => substr($item->getSku(), 0, 12)
                ]
            );

            $itemPrice = (float) $item->getPrice();
            $lineItems[] = array_combine(
                self::LINE_ITEMS_ARRAY,
                [
                    $filteredFields['name'],
                    TransactionLineItem::DEBIT,
                    $this->numberToString((float)$item->getQtyOrdered(), 2),
                    $this->numberToString($itemPrice, 2),
                    $filteredFields['unit_of_measure'],
                    $this->numberToString((float)$item->getQtyOrdered() * $itemPrice, 2),
                    $item->getTaxAmount() === null ? '0.00' : $this->numberToString($item->getTaxAmount(), 2),
                    $item->getDiscountAmount() === null ? '0.00' : $this->numberToString($item->getDiscountAmount(), 2),
                    $filteredFields['sku'],
                    $filteredFields['sku']
                ]
            );
        }

        $processingData = [
            self::KEY_PURCHASE_ORDER_NUMBER => substr($order->getOrderIncrementId(), -12, 12), // Level 2.
            self::KEY_TAX_AMT => $this->numberToString($order->getBaseTaxAmount(), 2), // Level 2.
            self::KEY_DISCOUNT_AMT => $this->numberToString(abs($order->getBaseDiscountAmount()), 2), // Level 3.
        ];

        if ($this->isSendLineItems()) {
            $processingData[self::KEY_LINE_ITEMS] = $lineItems; // Level 3.
        }


        // Only add these shipping related details if a shipping address is present.
        if ($order->getShippingAddress()) {
            $storePostalCode = $this->scopeConfig->getValue(
                'general/store_information/postcode',
                ScopeInterface::SCOPE_STORE
            );

            $address = $order->getShippingAddress();
            // use Magento's Alpha2 code to get the Alpha3 code.
            $country  = $this->country->loadByCode($address->getCountryId());

            // Level 3.
            $processingData[self::KEY_SHIPPING_AMT] = $this->numberToString($payment->getShippingAmount(), 2);
            $processingData[self::KEY_SHIPS_FROM_POSTAL_CODE] = $storePostalCode;
            $processingData[self::KEY_SHIPPING] = [
                self::KEY_COUNTRY_CODE_ALPHA_3 => $country['iso3_code'] ?? $address->getCountryId()
            ];
        }

        return $processingData;
    }

    /**
     * @param float $num
     * @param int $precision
     * @return string
     */
    private function numberToString($num, int $precision): string
    {
        // To counter the fact that Magento often wrongly returns a sting for price values, we can cast it to a float.
        if (is_string($num)) {
            $num = (float) $num;
        }

        return (string) round($num, $precision);
    }

    /**
     * @return bool
     */
    private function isSendLineItems()
    {
        return (bool) $this->scopeConfig->getValue(
            'payment/braintree/send_line_items',
            ScopeInterface::SCOPE_STORE
        );
    }
}
