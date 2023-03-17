<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace PayPal\Braintree\Gateway\Request;

use Braintree\TransactionLineItem;
use Magento\Directory\Model\Country;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Store\Model\ScopeInterface;
use PayPal\Braintree\Gateway\Config\Config;
use PayPal\Braintree\Gateway\Data\Order\OrderAdapter;
use PayPal\Braintree\Gateway\Helper\SubjectReader;

class Level23ProcessingDataBuilder implements BuilderInterface
{
    private const KEY_PURCHASE_ORDER_NUMBER = 'purchaseOrderNumber';
    private const KEY_TAX_AMT = 'taxAmount';
    private const KEY_SHIPPING_AMT = 'shippingAmount';
    private const KEY_DISCOUNT_AMT = 'discountAmount';
    private const KEY_SHIPS_FROM_POSTAL_CODE = 'shipsFromPostalCode';
    private const KEY_SHIPPING = 'shipping';
    private const KEY_COUNTRY_CODE_ALPHA_3 = 'countryCodeAlpha3';
    private const KEY_LINE_ITEMS = 'lineItems';
    private const LINE_ITEMS_ARRAY = [
        'name',
        'kind',
        'quantity',
        'unitAmount',
        'unitOfMeasure',
        'totalAmount',
        'taxAmount',
        'discountAmount',
        'productCode',
        'commodityCode',
        'description'
    ];

    /**
     * @var SubjectReader
     */
    private SubjectReader $subjectReader;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var Country
     */
    private Country $country;

    /**
     * @var Config
     */
    private Config $braintreeConfig;

    /**
     * @var quoteRepository
     */
    private QuoteRepository $quoteRepository;

    /**
     * Level23ProcessingDataBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     * @param ScopeConfigInterface $scopeConfig
     * @param Country $country
     * @param Config $braintreeConfig
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        SubjectReader $subjectReader,
        ScopeConfigInterface $scopeConfig,
        Country $country,
        Config $braintreeConfig,
        QuoteRepository $quoteRepository
    ) {
        $this->subjectReader = $subjectReader;
        $this->scopeConfig = $scopeConfig;
        $this->country = $country;
        $this->braintreeConfig = $braintreeConfig;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws NoSuchEntityException
     */
    public function build(array $buildSubject): array
    {
        $lineItems = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();

        /**
         * Override in di.xml, so we can add extra public methods.
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

            $description = '';
            $itemQuantity = (float) $item->getQtyOrdered();
            $itemUnitPrice = (float) $item->getPrice();

            if (($payment->getMethod() === 'braintree_paypal' || $payment->getMethod() === 'braintree_paypal_vault')) {
                if ($itemQuantity > floor($itemQuantity) && $itemQuantity < ceil($itemQuantity)) {
                    $description = 'Item quantity is ' . $this->numberToString($itemQuantity, 2) . ' and per unit amount is ' . $this->numberToString($itemUnitPrice, 2);
                    $itemUnitPrice = (float) $itemQuantity * $itemUnitPrice;
                    $itemQuantity = 1.00;
                }
            }

            $lineItems[] = array_combine(
                self::LINE_ITEMS_ARRAY,
                [
                    $filteredFields['name'],
                    TransactionLineItem::DEBIT,
                    $this->numberToString($itemQuantity, 2),
                    $this->numberToString($itemUnitPrice, 2),
                    $filteredFields['unit_of_measure'],
                    $this->numberToString((float) $item->getQtyOrdered() * $item->getPrice(), 2),
                    $item->getTaxAmount() === null ? '0.00' : $this->numberToString($item->getTaxAmount(), 2),
                    $item->getDiscountAmount() === null ? '0.00' : $this->numberToString($item->getDiscountAmount(), 2),
                    $filteredFields['sku'],
                    $filteredFields['sku'],
                    $description
                ]
            );
        }

        $baseDiscountAmount = $this->numberToString(abs($order->getBaseDiscountAmount()), 2);
        if (($payment->getMethod() === 'braintree_paypal' || $payment->getMethod() === 'braintree_paypal_vault')) {
            /**
             * Adds credit (refund or discount) kind as LineItems for the
             * PayPal transaction if discount amount is greater than 0(Zero)
             * as discountAmount lineItem field is not being used by PayPal.
             *
             * https://developer.paypal.com/braintree/docs/reference/response/transaction-line-item/php#discount_amount
             */
            if ($baseDiscountAmount > 0) {
                $discountLineItems[] = [
                    'name' => 'discount',
                    'kind' => TransactionLineItem::CREDIT,
                    'quantity' => 1.00,
                    'unitAmount' => $baseDiscountAmount,
                    'totalAmount' => $baseDiscountAmount
                ];

                $lineItems = array_merge($lineItems, $discountLineItems);
            }

            /** Get Order Extension Attributes */
            $extensionAttributes = $order->getExtensionAttributes();
            $gwBasePrice = $this->numberToString($extensionAttributes->getGwBasePrice(), 2);
            $gwItemsBasePrice = $this->numberToString($extensionAttributes->getGwItemsBasePrice(), 2);

            /** Get customer balance amount and gift cards amount from quote */
            $quote = $this->quoteRepository->get($order->getQuoteId());
            $baseCustomerBalAmountUsed = $this->numberToString(abs((float)$quote->getBaseCustomerBalAmountUsed()), 2);
            $baseGiftCardsAmountUsed = $this->numberToString(abs((float)$quote->getBaseGiftCardsAmountUsed()), 2);

            /**
             * Adds Gift Wrapping for Order as LineItems for the PayPal
             * transaction if it is greater than 0(Zero) to manage
             * the totals with server-side implementation as there is
             * no any field exist to send that amount to the Braintree.
             */
            if ($gwBasePrice > 0) {
                $gwBasePriceItems[] = [
                    'name' => 'Gift Wrapping for Order',
                    'kind' => TransactionLineItem::DEBIT,
                    'quantity' => 1.00,
                    'unitAmount' => $gwBasePrice,
                    'totalAmount' => $gwBasePrice
                ];

                $lineItems = array_merge($lineItems, $gwBasePriceItems);
            }

            /**
             * Adds Gift Wrapping for items as LineItems for the PayPal
             * transaction if it is greater than 0(Zero) to manage
             * the totals with server-side implementation as there is
             * no any field exist to send that amount to the Braintree.
             */
            if ($gwItemsBasePrice > 0) {
                $gwItemsBasePriceItems[] = [
                    'name' => 'Gift Wrapping for Items',
                    'kind' => TransactionLineItem::DEBIT,
                    'quantity' => 1.00,
                    'unitAmount' => $gwItemsBasePrice,
                    'totalAmount' => $gwItemsBasePrice
                ];

                $lineItems = array_merge($lineItems, $gwItemsBasePriceItems);
            }

            /**
             * Adds Store Credit as credit LineItems for the PayPal
             * transaction if store credit is greater than 0(Zero)
             * to manage the totals with server-side implementation
             * as there is no any field exist to send that amount
             * to the Braintree.
             */
            if ($baseCustomerBalAmountUsed > 0) {
                $storeCreditItems[] = [
                    'name' => 'Store Credit',
                    'kind' => TransactionLineItem::CREDIT,
                    'quantity' => 1.00,
                    'unitAmount' => $baseCustomerBalAmountUsed,
                    'totalAmount' => $baseCustomerBalAmountUsed
                ];

                $lineItems = array_merge($lineItems, $storeCreditItems);
            }

            /**
             * Adds Gift Cards as credit LineItems for the PayPal
             * transaction if it is greater than 0(Zero) to manage
             * the totals with server-side implementation as there is
             * no any field exist to send that amount to the Braintree.
             */
            if ($baseGiftCardsAmountUsed > 0) {
                $giftCardsItems[] = [
                    'name' => 'Gift Cards',
                    'kind' => TransactionLineItem::CREDIT,
                    'quantity' => 1.00,
                    'unitAmount' => $baseGiftCardsAmountUsed,
                    'totalAmount' => $baseGiftCardsAmountUsed
                ];

                $lineItems = array_merge($lineItems, $giftCardsItems);
            }
        }

        $processingData = [
            self::KEY_PURCHASE_ORDER_NUMBER => substr($order->getOrderIncrementId(), -12, 12), // Level 2.
            self::KEY_TAX_AMT => $this->numberToString($order->getBaseTaxAmount(), 2), // Level 2.
            self::KEY_DISCOUNT_AMT => $baseDiscountAmount, // Level 3.
        ];

        // Can send line items to braintree if enabled and line items are less than 250.
        if ($this->braintreeConfig->canSendLineItems() && count($lineItems) < 250) {
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
}
