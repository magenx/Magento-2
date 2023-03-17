<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use function array_values;

final class TestResult extends ExecutionContext
{
    protected ?string $historyId = null;

    protected ?string $testCaseId = null;

    protected ?string $rerunOf = null;

    protected ?string $fullName = null;

    /**
     * @var list<Label>
     */
    protected array $labels = [];

    /**
     * @var list<Link>
     */
    protected array $links = [];

    public function getResultType(): ResultType
    {
        return ResultType::test();
    }

    public function getHistoryId(): ?string
    {
        return $this->historyId;
    }

    public function setHistoryId(?string $historyId): self
    {
        $this->historyId = $historyId;

        return $this;
    }

    public function getTestCaseId(): ?string
    {
        return $this->testCaseId;
    }

    public function setTestCaseId(?string $testCaseId): self
    {
        $this->testCaseId = $testCaseId;

        return $this;
    }

    public function getRerunOf(): ?string
    {
        return $this->rerunOf;
    }

    public function setRerunOf(?string $rerunOf): self
    {
        $this->rerunOf = $rerunOf;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return list<Label>
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    public function addLabels(Label ...$labels): self
    {
        return $this->setLabels(...$this->labels, ...array_values($labels));
    }

    public function setLabels(Label ...$labels): self
    {
        $this->labels = array_values($labels);

        return $this;
    }

    /**
     * @return list<Link>
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    public function addLinks(Link ...$links): self
    {
        return $this->setLinks(...$this->links, ...array_values($links));
    }

    public function setLinks(Link ...$links): self
    {
        $this->links = array_values($links);

        return $this;
    }
}
