<?php
declare(strict_types=1);

namespace PayPal\Braintree\Api;

use Magento\Sales\Api\Data\OrderInterface;
use SimpleXMLElement;

/**
 * Interface EnsManagementInterface
 */
interface EnsManagementInterface
{
    // Kount config keys
    const CONFIG_KOUNT_ID = 'payment/braintree/kount_id';
    const CONFIG_ALLOWED_IPS = 'payment/braintree/kount_allowed_ips';
    const CONFIG_ENVIRONMENT = 'payment/braintree/kount_environment';

    // ENS type
    const ENS_WORKFLOW_EDIT = 'WORKFLOW_STATUS_EDIT';

    // Kount ENS response values
    const RESPONSE_DECLINE = 'D';
    const RESPONSE_APPROVE = 'A';
    const RESPONSE_REVIEW = 'R';
    const RESPONSE_ESCALATE = 'E';

    /**
     * @param string $ip
     * @return bool
     */
    public function isAllowed(string $ip): bool;

    /**
     * @param SimpleXMLElement $event
     * @return bool
     */
    public function processEvent(SimpleXMLElement $event): bool;

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function approveOrder(OrderInterface $order): bool;

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function declineOrder(OrderInterface $order): bool;

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function voidOrder(OrderInterface $order): bool;

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function refundOrder(OrderInterface $order): bool;
}
