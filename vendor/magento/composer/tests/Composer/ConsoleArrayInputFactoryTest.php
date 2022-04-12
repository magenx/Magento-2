<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Composer\ConsoleArrayInputFactory;

class ConsoleArrayInputFactoryTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ConsoleArrayInputFactory
     */
    protected $factory;

    protected function setUp(): void
    {
        $this->factory = new ConsoleArrayInputFactory();
    }

    public function testCreate()
    {
        $this->assertInstanceOf(\Symfony\Component\Console\Input\ArrayInput::class, $this->factory->create([]));
    }
}
