<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Test\Unit\Gateway\Request;

use PayPal\Braintree\Gateway\Request\ChannelDataBuilder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChannelDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ChannelDataBuilder
     */
    private $builder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->builder = new ChannelDataBuilder();
    }

    /**
     * @param array $expected
     * @covers \PayPal\Braintree\Gateway\Request\ChannelDataBuilder::build
     * @dataProvider buildDataProvider
     */
    public function testBuild(array $expected)
    {
        self::assertEquals($expected, $this->builder->build([]));
    }

    /**
     * Get list of variations for build test
     * @return string
     */
    public function buildDataProvider()
    {
        return 'Magento2GeneBT';
    }
}
