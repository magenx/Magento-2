<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\App\ProductMetadataInterface;

class ChannelDataBuilder implements BuilderInterface
{
    /**
     * @var string $channel
     */
    private static $channel = 'channel';

    /**
     * @var string $channelValue
     */
    private static $channelValue = 'Magento2GeneBT';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        return [
            self::$channel => self::$channelValue
        ];
    }
}
