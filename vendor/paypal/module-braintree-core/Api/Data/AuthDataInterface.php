<?php

namespace PayPal\Braintree\Api\Data;

/**
 * Interface AuthDataInterface
 * @api
 **/
interface AuthDataInterface
{
    /**
     * Braintree client token
     *
     * @return string|null
     */
    public function getClientToken();

    /**
     * Merchant display name
     *
     * @return string
     */
    public function getDisplayName(): string;

    /**
     * URL To success page
     *
     * @return string
     */
    public function getActionSuccess(): string;

    /**
     * @return bool
     */
    public function isLoggedIn(): bool;

    /**
     * Get current store code
     *
     * @return string
     */
    public function getStoreCode(): string;

    /**
     * Set Braintree client token
     *
     * @param string $value
     * @return string|null
     */
    public function setClientToken($value);

    /**
     * Set Merchant display name
     *
     * @param string $value
     * @return string|null
     */
    public function setDisplayName($value);

    /**
     * Set URL To success page
     *
     * @param string $value
     * @return string|null
     */
    public function setActionSuccess($value);

    /**
     * Set if user is logged in
     *
     * @param bool $value
     * @return bool|null
     */
    public function setIsLoggedIn($value);

    /**
     * Set current store code
     *
     * @param string $value
     * @return string|null
     */
    public function setStoreCode($value);
}
