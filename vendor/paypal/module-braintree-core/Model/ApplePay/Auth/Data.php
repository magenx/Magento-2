<?php

namespace PayPal\Braintree\Model\ApplePay\Auth;

use PayPal\Braintree\Api\Data\AuthDataInterface;

class Data implements AuthDataInterface
{
    /**
     * @var string $clientToken
     */
    private $clientToken;

    /**
     * @var string $displayName
     */
    private $displayName;

    /**
     * @var string $actionSuccess
     */
    private $actionSuccess;

    /**
     * @var bool $isLoggedIn
     */
    private $isLoggedIn;

    /**
     * @var string $storeCode
     */
    private $storeCode;

    /**
     * @inheritdoc
     */
    public function getClientToken()
    {
        return $this->clientToken;
    }

    /**
     * @inheritdoc
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @inheritdoc
     */
    public function getActionSuccess(): string
    {
        return $this->actionSuccess;
    }

    /**
     * @inheritdoc
     */
    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }

    /**
     * @inheritdoc
     */
    public function getStoreCode(): string
    {
        return $this->storeCode;
    }

    /**
     * @inheritdoc
     */
    public function setClientToken($value)
    {
        $this->clientToken = $value;
    }

    /**
     * @inheritdoc
     */
    public function setDisplayName($value)
    {
        $this->displayName = $value;
    }

    /**
     * @inheritdoc
     */
    public function setActionSuccess($value)
    {
        $this->actionSuccess = $value;
    }

    /**
     * @inheritdoc
     */
    public function setIsLoggedIn($value)
    {
        $this->isLoggedIn = $value;
    }

    /**
     * @inheritdoc
     */
    public function setStoreCode($value)
    {
        $this->storeCode = $value;
    }
}
