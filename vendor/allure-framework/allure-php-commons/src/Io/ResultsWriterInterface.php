<?php

declare(strict_types=1);

namespace Qameta\Allure\Io;

use Qameta\Allure\Model\AttachmentResult;
use Qameta\Allure\Model\ContainerResult;
use Qameta\Allure\Model\TestResult;

interface ResultsWriterInterface
{
    public function writeContainer(ContainerResult $container): void;

    public function writeTest(TestResult $test): void;

    public function writeAttachment(AttachmentResult $attachment, DataSourceInterface $data): void;

    public function removeAttachment(AttachmentResult $attachment): void;

    public function removeTest(TestResult $test): void;
}
