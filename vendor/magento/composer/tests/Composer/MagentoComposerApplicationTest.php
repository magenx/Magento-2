<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Composer\Console\Application;
use Magento\Composer\MagentoComposerApplication;
use Magento\Composer\ConsoleArrayInputFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Output\BufferedOutput;

class MagentoComposerApplicationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MagentoComposerApplication
     */
    protected $application;

    /**
     * @var Application|MockObject
     */
    protected $composerApplication;

    /**
     * @var ConsoleArrayInputFactory|MockObject
     */
    protected $inputFactory;

    /**
     * @var BufferedOutput|MockObject
     */
    protected $consoleOutput;

    protected function setUp(): void
    {
        $this->composerApplication = $this->createMock(\Composer\Console\Application::class);
        $this->inputFactory = $this->createMock(\Magento\Composer\ConsoleArrayInputFactory::class);
        $this->consoleOutput = $this->createMock(\Symfony\Component\Console\Output\BufferedOutput::class);

        $this->application = new MagentoComposerApplication(
            'path1',
            'path2',
            $this->composerApplication,
            $this->inputFactory,
            $this->consoleOutput
        );
    }

    function testWrongExitCode()
    {
        $this->composerApplication->expects($this->once())->method('run')->willReturn(1);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Command "update" failed');

        $this->application->runComposerCommand(['command'=>'update']);
    }

    function testRunCommand()
    {
        $inputData = ['command' => 'update', MagentoComposerApplication::COMPOSER_WORKING_DIR => '.'];

        $this->composerApplication->expects($this->once())->method('resetComposer');

        $this->inputFactory->expects($this->once())->method('create')->with($inputData);

        $this->consoleOutput->expects($this->once())->method('fetch')->willReturn('Nothing to update');

        $this->composerApplication->expects($this->once())->method('run')->willReturn(0);

        $message = $this->application->runComposerCommand($inputData);
        $this->assertEquals('Nothing to update', $message);
    }
}
