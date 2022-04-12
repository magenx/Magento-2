<?php
declare(strict_types=1);

namespace PayPal\Braintree\Controller\Kount;

use Exception;
use PayPal\Braintree\Model\Kount\EnsConfig;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Xml\Security;

/**
 * Acts as the entry point for the Kount ENS.
 */
class Ens extends Action
{
    const KOUNT_MERCHANT_ID = 'payment/braintree/kount_id';
    /**
     * @var EnsConfig
     */
    private $ensConfig;
    /**
     * @var RemoteAddress
     */
    private $remoteAddress;
    /**
     * @var Security
     */
    private $xmlSecurity;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param EnsConfig $ensConfig
     * @param RemoteAddress $remoteAddress
     * @param Security $xmlSecurity
     */
    public function __construct(
        Context $context,
        EnsConfig $ensConfig,
        RemoteAddress $remoteAddress,
        Security $xmlSecurity
    ) {
        parent::__construct($context);
        $this->ensConfig = $ensConfig;
        $this->remoteAddress = $remoteAddress;
        $this->xmlSecurity = $xmlSecurity;
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute()
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if (!$this->isAllowed()) {
            $response->setHttpResponseCode(401);
            return $response;
        }

        $request = $this->getRequest()->getContent();

        if (!$this->xmlSecurity->scan($request)) {
            $response->setHttpResponseCode(400);
            return $response;
        }

        $xml = simplexml_load_string($request);

        if (empty($xml['merchant'])) {
            throw new LocalizedException(__('Invalid ENS XML'));
        }

        if (!$this->ensConfig->validateMerchantId((string) $xml['merchant'])) {
            throw new LocalizedException(__('Invalid Merchant ID'));
        }

        foreach ($xml->children() as $event) {
            $this->ensConfig->processEvent($event);
        }

        return $response;
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        return $this->ensConfig->isAllowed($this->remoteAddress->getRemoteAddress());
    }
}
