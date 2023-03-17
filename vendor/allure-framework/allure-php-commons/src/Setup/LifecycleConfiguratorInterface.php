<?php

declare(strict_types=1);

namespace Qameta\Allure\Setup;

use Psr\Log\LoggerInterface;
use Qameta\Allure\Hook\LifecycleHookInterface;
use Qameta\Allure\Io\ClockInterface;
use Qameta\Allure\Model\LinkType;
use Qameta\Allure\Model\ResultFactoryInterface;
use Ramsey\Uuid\UuidFactoryInterface;

interface LifecycleConfiguratorInterface
{
    public function setOutputDirectory(string $outputDirectory): self;

    public function setResultFactory(ResultFactoryInterface $resultFactory): self;

    public function setStatusDetector(StatusDetectorInterface $statusDetector): self;

    public function setUuidFactory(UuidFactoryInterface $uuidFactory): self;

    public function setLogger(LoggerInterface $logger): self;

    public function setClock(ClockInterface $clock): self;

    public function addHooks(LifecycleHookInterface ...$hooks): self;

    public function addLinkTemplate(LinkType $type, LinkTemplateInterface $template): self;
}
