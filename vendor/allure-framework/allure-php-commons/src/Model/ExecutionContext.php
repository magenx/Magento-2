<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

use function array_values;

abstract class ExecutionContext extends StorableResult implements ExecutionContextInterface
{
    protected ?Status $status = null;

    protected ?StatusDetails $statusDetails = null;

    protected ?Stage $stage = null;

    /**
     * @var list<StepResult>
     */
    protected array $steps = [];

    /**
     * @var list<AttachmentResult>
     */
    protected array $attachments = [];

    /**
     * @var list<Parameter>
     */
    protected array $parameters = [];

    final public function getStatus(): ?Status
    {
        return $this->status;
    }

    final public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    final public function getStatusDetails(): ?StatusDetails
    {
        return $this->statusDetails;
    }

    final public function setStatusDetails(?StatusDetails $statusDetails): static
    {
        $this->statusDetails = $statusDetails;

        return $this;
    }

    final public function getStage(): ?Stage
    {
        return $this->stage;
    }

    final public function setStage(?Stage $stage): static
    {
        $this->stage = $stage;

        return $this;
    }

    /**
     * @return list<StepResult>
     */
    final public function getSteps(): array
    {
        return $this->steps;
    }

    final public function addSteps(StepResult ...$steps): static
    {
        return $this->setSteps(...$this->steps, ...array_values($steps));
    }

    final public function setSteps(StepResult ...$steps): static
    {
        $this->steps = array_values($steps);

        return $this;
    }

    /**
     * @return list<AttachmentResult>
     */
    final public function getAttachments(): array
    {
        return $this->attachments;
    }

    final public function addAttachments(AttachmentResult ...$attachments): static
    {
        return $this->setAttachments(...$this->attachments, ...array_values($attachments));
    }

    final public function setAttachments(AttachmentResult ...$attachments): static
    {
        $this->attachments = array_values($attachments);

        return $this;
    }

    /**
     * @return list<Parameter>
     */
    final public function getParameters(): array
    {
        return $this->parameters;
    }

    final public function addParameters(Parameter ...$parameters): static
    {
        return $this->setParameters(...$this->parameters, ...array_values($parameters));
    }

    final public function setParameters(Parameter ...$parameters): static
    {
        $this->parameters = array_values($parameters);

        return $this;
    }

    /**
     * @return list<ResultInterface>
     */
    final public function getNestedResults(): array
    {
        return [
            ...$this->attachments,
            ...$this->steps,
        ];
    }
}
