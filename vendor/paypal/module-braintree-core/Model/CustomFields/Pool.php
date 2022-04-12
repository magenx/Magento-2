<?php

namespace PayPal\Braintree\Model\CustomFields;

use InvalidArgumentException;

class Pool
{
    /**
     * @var array
     */
    protected $fieldsPool;

    /**
     * CustomFieldsDataBuilder constructor.
     * @param array $fields
     */
    public function __construct($fields = [])
    {
        $this->fieldsPool = $fields;
        $this->checkFields();
    }

    /**
     * @param $buildSubject
     * @return array
     */
    public function getFields($buildSubject): array
    {
        $result = [];

        /** @var CustomFieldInterface $field */
        foreach ($this->fieldsPool as $field) {
            $result[ $field->getApiName() ] = $field->getValue($buildSubject);
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function checkFields(): bool
    {
        foreach ($this->fieldsPool as $field) {
            if (!($field instanceof CustomFieldInterface)) {
                throw new InvalidArgumentException('Custom field must implement CustomFieldInterface');
            }
        }
        return true;
    }
}
