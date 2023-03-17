<?php

declare(strict_types=1);

namespace Qameta\Allure\Model;

interface ExecutionContextInterface extends StorableResultInterface
{
    public function getStage(): ?Stage;

    public function setStage(?Stage $stage): static;

    public function getStatus(): ?Status;

    public function setStatus(?Status $status): static;

    public function getStatusDetails(): ?StatusDetails;

    public function setStatusDetails(?StatusDetails $statusDetails): static;

    /**
     * @return list<Parameter>
     */
    public function getParameters(): array;

    public function addParameters(Parameter ...$parameters): static;

    public function setParameters(Parameter ...$parameters): static;

    /**
     * @return list<AttachmentResult>
     */
    public function getAttachments(): array;

    public function addAttachments(AttachmentResult ...$attachments): static;

    public function setAttachments(AttachmentResult ...$attachments): static;

    /**
     * @return list<StepResult>
     */
    public function getSteps(): array;

    public function addSteps(StepResult ...$steps): static;

    public function setSteps(StepResult ...$steps): static;
}
