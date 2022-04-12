<?php
declare(strict_types=1);

namespace PayPal\Braintree\Plugin;

use \Magento\Framework\View\Asset\Minification;

class ExcludeFromMinification
{
    /**
     * @param Minification $subject
     * @param array $result
     * @param $contentType
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetExcludes(Minification $subject, array $result, $contentType): array
    {
        $result[] = '/pay.google.com/';
        return $result;
    }
}
