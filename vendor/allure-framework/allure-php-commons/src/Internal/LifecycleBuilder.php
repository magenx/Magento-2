<?php

declare(strict_types=1);

namespace Qameta\Allure\Internal;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Qameta\Allure\AllureLifecycle;
use Qameta\Allure\AllureLifecycleInterface;
use Qameta\Allure\Exception\OutputDirectoryUndefinedException;
use Qameta\Allure\Hook\LifecycleHookInterface;
use Qameta\Allure\Io\ClockInterface;
use Qameta\Allure\Io\FileSystemResultsWriter;
use Qameta\Allure\Io\ResultsWriterInterface;
use Qameta\Allure\Io\SystemClock;
use Qameta\Allure\Model\LinkType;
use Qameta\Allure\Model\ResultFactory;
use Qameta\Allure\Model\ResultFactoryInterface;
use Qameta\Allure\Setup\DefaultStatusDetector;
use Qameta\Allure\Setup\LifecycleBuilderInterface;
use Qameta\Allure\Setup\LifecycleConfiguratorInterface;
use Qameta\Allure\Setup\LinkTemplateCollection;
use Qameta\Allure\Setup\LinkTemplateCollectionInterface;
use Qameta\Allure\Setup\LinkTemplateInterface;
use Qameta\Allure\Setup\StatusDetectorInterface;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;

use function array_values;

final class LifecycleBuilder implements LifecycleBuilderInterface
{
    private ?string $outputDirectory = null;

    private ?UuidFactoryInterface $uuidFactory = null;

    private ?LoggerInterface $logger = null;

    private ?ClockInterface $clock = null;

    private ?ResultFactoryInterface $resultFactory = null;

    /**
     * @var list<LifecycleHookInterface>
     */
    private array $lifecycleHooks = [];

    /**
     * @var array<string, LinkTemplateInterface>
     */
    private array $linkTemplates = [];

    private ?LinkTemplateCollectionInterface $linkTemplateCollection = null;

    private ?StatusDetectorInterface $statusDetector = null;

    public function createLifecycle(ResultsWriterInterface $resultsWriter): AllureLifecycleInterface
    {
        return new AllureLifecycle(
            $this->getLogger(),
            $this->getClock(),
            $resultsWriter,
            new HooksNotifier($this->getLogger(), ...$this->lifecycleHooks),
            new ResultStorage(),
            new ThreadContext(),
        );
    }

    public function setOutputDirectory(string $outputDirectory): LifecycleConfiguratorInterface
    {
        $this->outputDirectory = $outputDirectory;

        return $this;
    }

    public function createResultsWriter(): ResultsWriterInterface
    {
        return new FileSystemResultsWriter(
            $this->getOutputDirectory(),
            $this->getLogger(),
        );
    }

    private function getOutputDirectory(): string
    {
        return $this->outputDirectory ?? throw new OutputDirectoryUndefinedException();
    }

    public function setResultFactory(ResultFactoryInterface $resultFactory): self
    {
        $this->resultFactory = $resultFactory;

        return $this;
    }

    public function getResultFactory(): ResultFactoryInterface
    {
        return $this->resultFactory ??= new ResultFactory(
            $this->getUuidFactory(),
        );
    }

    public function addHooks(LifecycleHookInterface ...$hooks): self
    {
        $this->lifecycleHooks = [...$this->lifecycleHooks, ...array_values($hooks)];

        return $this;
    }

    public function addLinkTemplate(LinkType $type, LinkTemplateInterface $template): LifecycleConfiguratorInterface
    {
        $this->linkTemplates[(string) $type] = $template;

        return $this;
    }

    public function getLinkTemplates(): LinkTemplateCollectionInterface
    {
        return $this->linkTemplateCollection ??= new LinkTemplateCollection($this->linkTemplates);
    }

    public function setStatusDetector(StatusDetectorInterface $statusDetector): self
    {
        $this->statusDetector = $statusDetector;

        return $this;
    }

    public function getStatusDetector(): StatusDetectorInterface
    {
        return $this->statusDetector ??= new DefaultStatusDetector();
    }

    public function setUuidFactory(UuidFactoryInterface $uuidFactory): self
    {
        $this->uuidFactory = $uuidFactory;

        return $this;
    }

    private function getUuidFactory(): UuidFactoryInterface
    {
        return $this->uuidFactory ??= new UuidFactory();
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    private function getLogger(): LoggerInterface
    {
        return $this->logger ??= new NullLogger();
    }

    public function setClock(ClockInterface $clock): self
    {
        $this->clock = $clock;

        return $this;
    }

    private function getClock(): ClockInterface
    {
        return $this->clock ??= new SystemClock();
    }
}
