<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Braintree\Plugin;

use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\ReCaptchaValidationApi\Api\ValidatorInterface;
use Magento\ReCaptchaWebapiApi\Api\WebapiValidationConfigProviderInterface;
use Magento\ReCaptchaWebapiApi\Model\Data\EndpointFactory;
use Magento\Webapi\Controller\Rest\RequestValidator;
use Magento\Webapi\Controller\Rest\Router;
use PayPal\Braintree\Model\Recaptcha\WebapiConfigProvider;
use PayPal\Braintree\Model\Ui\ConfigProvider as Braintree;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;

/**
 * Enable ReCaptcha validation for RESTful web API.
 */
class RestValidationPlugin
{
    /**
     * @var WebapiValidationConfigProviderInterface
     */
    private WebapiValidationConfigProviderInterface $configProvider;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $recaptchaValidator;

    /**
     * @var RestRequest
     */
    private RestRequest $request;

    /**
     * @var EndpointFactory
     */
    private EndpointFactory $endpointFactory;

    /**
     * @var Router
     */
    private Router $restRouter;

    /**
     * @var IsCaptchaEnabledInterface
     */
    private $isEnabled;

    /**
     * @param ValidatorInterface $recaptchaValidator
     * @param WebapiValidationConfigProviderInterface $configProvider
     * @param RestRequest $request
     * @param Router $restRouter
     * @param EndpointFactory $endpointFactory
     * @param IsCaptchaEnabledInterface $isEnabled
     */
    public function __construct(
        ValidatorInterface $recaptchaValidator,
        WebapiValidationConfigProviderInterface $configProvider,
        RestRequest $request,
        Router $restRouter,
        EndpointFactory $endpointFactory,
        IsCaptchaEnabledInterface $isEnabled
    ) {
        $this->recaptchaValidator = $recaptchaValidator;
        $this->configProvider = $configProvider;
        $this->request = $request;
        $this->restRouter = $restRouter;
        $this->endpointFactory = $endpointFactory;
        $this->isEnabled = $isEnabled;
    }

    /**
     * Validate ReCaptcha if needed.
     *
     * @param RequestValidator $subject
     * @param callable $proceed
     * @throws WebapiException
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate(RequestValidator $subject, callable $proceed): void
    {
        if (isset($this->request->getRequestData()['paymentMethod']['method']) &&
            $this->request->getRequestData()['paymentMethod']['method'] !== Braintree::CODE &&
            $this->isEnabled->isCaptchaEnabledFor(WebapiConfigProvider::CAPTCHA_ID)
        ) {
            $proceed();
            return;
        }

        $request = clone $this->request;
        $proceed();
        $route = $this->restRouter->match($request);
        $endpointData = $this->endpointFactory->create([
            'class' => $route->getServiceClass(),
            'method' => $route->getServiceMethod(),
            'name' => $route->getRoutePath()
        ]);
        $config = $this->configProvider->getConfigFor($endpointData);
        if ($config) {
            $value = (string)$this->request->getHeader('X-ReCaptcha');
            if (!$this->recaptchaValidator->isValid($value, $config)->isValid()) {
                throw new WebapiException(__('ReCaptcha validation failed, please try again'));
            }
        }
    }
}
