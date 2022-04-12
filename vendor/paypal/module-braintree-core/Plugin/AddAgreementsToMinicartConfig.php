<?php

declare(strict_types=1);

namespace PayPal\Braintree\Plugin;

use Magento\Checkout\Block\Cart\Sidebar;
use Magento\CheckoutAgreements\Model\AgreementsConfigProvider;

/**
 * Class AddAgreementsToMinicartConfig
 * A plugin class to add agreements ids to the minicart config
 */
class AddAgreementsToMinicartConfig
{
    /**
     * @var AgreementsConfigProvider
     */
    private $agreementsConfigProvider;

    /**
     * AddAgreementsToMinicartConfig constructor.
     * @param AgreementsConfigProvider $agreementsConfigProvider
     */
    public function __construct(
        AgreementsConfigProvider $agreementsConfigProvider
    ) {
        $this->agreementsConfigProvider = $agreementsConfigProvider;
    }

    /**
     * @param Sidebar $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(Sidebar $subject, array $result): array
    {
        $checkoutAgreements = $this->agreementsConfigProvider->getConfig();
        if (isset($checkoutAgreements['checkoutAgreements']['agreements'])) {
            foreach ($checkoutAgreements['checkoutAgreements']['agreements'] as $agreement) {
                $result['agreementIds'][] = $agreement['agreementId'];
            }
        }
        return $result;
    }
}
