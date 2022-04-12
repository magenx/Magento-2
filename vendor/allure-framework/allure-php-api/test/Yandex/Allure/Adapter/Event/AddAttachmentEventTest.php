<?php

namespace Yandex\Allure\Adapter\Event;

use PHPUnit\Framework\TestCase;
use Yandex\Allure\Adapter\Model\Provider;
use Yandex\Allure\Adapter\Model\Step;

class AddAttachmentEventTest extends TestCase
{
    private const ATTACHMENT_CAPTION = 'test-caption';

    public function testEventWithFile(): void
    {
        $attachmentCaption = self::ATTACHMENT_CAPTION;
        $attachmentType = 'application/json';
        $correctExtension = 'json';
        $tmpDirectory = sys_get_temp_dir();
        Provider::setOutputDirectory($tmpDirectory);
        $tmpFilename = tempnam($tmpDirectory, 'allure-test');
        file_put_contents($tmpFilename, $this->getTestContents());
        $sha1Sum = sha1_file($tmpFilename);

        $event = new AddAttachmentEvent($tmpFilename, $attachmentCaption, $attachmentType);
        $step = new Step();
        $event->process($step);

        $attachmentFileName = $event->getOutputFileName($sha1Sum, $correctExtension);
        $attachmentOutputPath = $event->getOutputPath($sha1Sum, $correctExtension);
        $this->checkAttachmentIsCorrect(
            $step,
            $attachmentOutputPath,
            $attachmentFileName,
            $attachmentCaption,
            $attachmentType
        );
    }

    public function testEventWithStringContents(): void
    {
        $attachmentCaption = self::ATTACHMENT_CAPTION;
        $attachmentType = 'text/plain';
        $correctExtension = 'txt';
        $tmpDirectory = sys_get_temp_dir();
        Provider::setOutputDirectory($tmpDirectory);
        $contents = $this->getTestContents();
        $sha1Sum = sha1($contents);

        $event = new AddAttachmentEvent($contents, $attachmentCaption);
        $step = new Step();
        $event->process($step);

        $attachmentFileName = $event->getOutputFileName($sha1Sum, $correctExtension);
        $attachmentOutputPath = $event->getOutputPath($sha1Sum, $correctExtension);
        $this->checkAttachmentIsCorrect(
            $step,
            $attachmentOutputPath,
            $attachmentFileName,
            $attachmentCaption,
            $attachmentType
        );
    }

    private function checkAttachmentIsCorrect(
        Step $step,
        $attachmentOutputPath,
        $attachmentFileName,
        $attachmentCaption,
        $attachmentType
    ): void {
        $this->assertTrue(file_exists($attachmentOutputPath));
        $attachments = $step->getAttachments();
        $this->assertEquals(1, sizeof($attachments));
        $attachment = array_pop($attachments);
        $this->assertInstanceOf('Yandex\Allure\Adapter\Model\Attachment', $attachment);
        $this->assertEquals($attachmentFileName, $attachment->getSource());
        $this->assertEquals($attachmentCaption, $attachment->getTitle());
        $this->assertEquals($attachmentType, $attachment->getType());
    }

    private function getTestContents(): string
    {
        return str_shuffle('test-contents');
    }
}
