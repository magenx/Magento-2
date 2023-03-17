<?php

declare(strict_types=1);

namespace Qameta\Allure\Setup;

use Qameta\Allure\AllureLifecycleInterface;
use Qameta\Allure\Io\ResultsWriterInterface;

interface LifecycleFactoryInterface
{
    public function createLifecycle(ResultsWriterInterface $resultsWriter): AllureLifecycleInterface;

    public function createResultsWriter(): ResultsWriterInterface;
}
