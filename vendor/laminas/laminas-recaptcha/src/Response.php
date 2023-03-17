<?php

declare(strict_types=1);

namespace Laminas\ReCaptcha;

use Laminas\Http\Response as HTTPResponse;

use function array_key_exists;
use function is_array;
use function is_string;
use function json_decode;
use function trim;

use const JSON_THROW_ON_ERROR;

/**
 * Model responses from the ReCaptcha and MailHide APIs.
 *
 * @final This class should not be extended and will be marked final in version 4.0
 */
class Response
{
    /**
     * Status
     *
     * true if the response is valid or false otherwise
     *
     * @var bool
     */
    protected $status;

    /**
     * Error codes
     *
     * The error codes if the status is false. The different error codes can be found in the
     * recaptcha API docs.
     *
     * @var array
     */
    protected $errorCodes = [];

    /**
     * Class constructor used to construct a response
     *
     * @param bool|null $status
     * @param array $errorCodes
     * @param null|HTTPResponse $httpResponse If this is set the content will override $status and $errorCode
     */
    public function __construct($status = null, $errorCodes = [], ?HTTPResponse $httpResponse = null)
    {
        if ($status !== null) {
            $this->setStatus($status);
        }

        if (! empty($errorCodes)) {
            $this->setErrorCodes($errorCodes);
        }

        if ($httpResponse !== null) {
            $this->setFromHttpResponse($httpResponse);
        }
    }

    /**
     * Set the status
     *
     * @param bool $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = (bool) $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Alias for getStatus()
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->getStatus();
    }

    /**
     * Set the error codes
     *
     * @param string|array $errorCodes
     * @return self
     */
    public function setErrorCodes($errorCodes)
    {
        if (is_string($errorCodes)) {
            $errorCodes = [$errorCodes];
        }

        $this->errorCodes = $errorCodes;

        return $this;
    }

    /**
     * Get the error codes
     *
     * @return array
     */
    public function getErrorCodes()
    {
        return $this->errorCodes;
    }

    /**
     * Populate this instance based on a Laminas_Http_Response object
     *
     * @return self
     */
    public function setFromHttpResponse(HTTPResponse $response)
    {
        $body  = $response->getBody();
        $parts = '' !== trim($body) ? json_decode($body, true, 512, JSON_THROW_ON_ERROR) : [];

        $status     = false;
        $errorCodes = [];

        if (is_array($parts) && array_key_exists('success', $parts)) {
            $status = $parts['success'];
            if (array_key_exists('error-codes', $parts)) {
                $errorCodes = $parts['error-codes'];
            }
        }

        $this->setStatus($status);
        $this->setErrorCodes($errorCodes);

        return $this;
    }
}
