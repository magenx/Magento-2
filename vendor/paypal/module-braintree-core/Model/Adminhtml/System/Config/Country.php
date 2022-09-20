<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model\Adminhtml\System\Config;

use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\Option\ArrayInterface;

class Country implements ArrayInterface
{
    /**
     * @var array $options
     */
    protected $options;

    /**
     * Countries
     *
     * @var Collection $countryCollection
     */
    protected $countryCollection;

    /**
     * Countries not supported by Braintree
     *
     * @var array $excludedCountries
     */
    protected static $excludedCountries = [
        'MM',
        'IR',
        'SD',
        'BY',
        'CI',
        'CD',
        'CG',
        'IQ',
        'LR',
        'LB',
        'KP',
        'SL',
        'SY',
        'ZW',
        'AL',
        'BA',
        'MK',
        'ME',
        'RS'
    ];

    /**
     * @param Collection $countryCollection
     */
    public function __construct(Collection $countryCollection)
    {
        $this->countryCollection = $countryCollection;
    }

    /**
     * Get countries option array
     *
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArray($isMultiSelect = false): array
    {
        if (!$this->options) {
            $this->options = $this->countryCollection
                ->addFieldToFilter('country_id', ['nin' => $this->getExcludedCountries()])
                ->loadData()
                ->toOptionArray(false);
        }

        $options = $this->options;
        if (!$isMultiSelect) {
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        }

        return $options;
    }

    /**
     * If country is in list of restricted (not supported by Braintree)
     *
     * @param string $countryId
     * @return boolean
     */
    public function isCountryRestricted($countryId): bool
    {
        return in_array($countryId, $this->getExcludedCountries());
    }

    /**
     * Return list of excluded countries
     *
     * @return array
     */
    public function getExcludedCountries(): array
    {
        return self::$excludedCountries;
    }
}
