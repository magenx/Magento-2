<?php
declare(strict_types=1);

namespace PayPal\Braintree\Model\Recaptcha;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\ReCaptchaFrontendUi\Model\CaptchaTypeResolver;
use Magento\ReCaptchaValidationApi\Api\ValidatorInterface;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use Magento\ReCaptchaUi\Model\ValidationConfigResolver;
use PayPal\Braintree\Observer\DataAssignObserver;
use Magento\Framework\Exception\InputException;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;

class ReCaptchaValidation
{
    /**
     * @var SubjectReader $subjectReader
     */
    private $subjectReader;

    /**
     * @var CaptchaTypeResolver $captchaTypeResolverFrontEnd
     */
    private $captchaTypeResolverFrontEnd;

    /**
     * @var ValidationConfigResolver $validationConfigResolver
     */
    private $validationConfigResolver;

    /**
     * @var \Magento\Framework\ObjectManagerInterface $objectManager
     */
    private $objectManager;

    /**
     * @var ValidatorInterface
     */
    private $captchaValidator;

    /**
     * @var State
     */
    protected $state;

    /**
     * @param SubjectReader $subjectReader
     * @param CaptchaTypeResolver $captchaTypeResolverFrontEnd
     * @param ValidationConfigResolver $validationConfigResolver
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param ValidatorInterface $captchaValidator
     * @param State $state
     * @throws InputException
     */
    public function __construct(
        SubjectReader $subjectReader,
        CaptchaTypeResolver $captchaTypeResolverFrontEnd,
        ValidationConfigResolver $validationConfigResolver,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        ValidatorInterface $captchaValidator,
        State $state
    ) {
        $this->subjectReader = $subjectReader;
        $this->captchaTypeResolverFrontEnd = $captchaTypeResolverFrontEnd;
        $this->validationConfigResolver = $validationConfigResolver;
        $this->objectManager = $objectmanager;
        $this->captchaValidator = $captchaValidator;
        $this->state = $state;
    }

    /**
     * @inheritdoc
     */
    public function validate($payment)
    {
        $captchaType = $this->captchaTypeResolverFrontEnd->getCaptchaTypeFor('braintree');

        $className = $this->getReCaptchaClassName($captchaType);
        $validationConfig = $this->objectManager->create($className)->get();
        $paymentDO = $this->subjectReader->readPayment($payment);
        $payment = $paymentDO->getPayment();

        $token = $payment->getAdditionalInformation(
            DataAssignObserver::CAPTCHA_RESPONSE
        );

        if (
            in_array($this->state->getAreaCode(), [Area::AREA_ADMINHTML, Area::AREA_CRONTAB])
            || ($payment->getMethod() !== 'braintree')
            || !$captchaType
            || empty($token)
        ) {
            return;
        }

        $validationResult = $this->captchaValidator->isValid($token, $validationConfig);

        if (false === $validationResult->isValid()) {
            throw new CommandException(__(
                'reCAPTCHA validation error: %1',
                current($validationResult->getErrors())
            ));
        }
    }

    public function getReCaptchaClassName($captchaType)
    {
        $className = null;
        switch ($captchaType) {
            case 'recaptcha_v3':
                $className = '\Magento\ReCaptchaVersion3Invisible\Model\Frontend\ValidationConfigProvider';
                break;
            case 'invisible':
                $className = '\Magento\ReCaptchaVersion2Invisible\Model\Frontend\ValidationConfigProvider';
                break;
            case 'recaptcha':
                $className = '\Magento\ReCaptchaVersion2Checkbox\Model\Frontend\ValidationConfigProvider';
                break;
        }
        if (is_null($className)) {
            throw new InputException(
                __('Validation config provider for "%type" is not configured.', ['type' => $captchaType])
            );
        }

        return $className;
    }
}