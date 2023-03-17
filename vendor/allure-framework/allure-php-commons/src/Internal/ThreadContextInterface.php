<?php

declare(strict_types=1);

namespace Qameta\Allure\Internal;

interface ThreadContextInterface
{
    public function switchThread(?string $thread): ThreadContextInterface;

    public function reset(): ThreadContextInterface;

    public function push(string $uuid): ThreadContextInterface;

    public function pop(): ThreadContextInterface;

    public function getCurrentTest(): ?string;

    public function getCurrentStep(): ?string;

    public function getCurrentTestOrStep(): ?string;

    public function resetContainer(): ThreadContextInterface;

    public function setContainer(string $uuid): ThreadContextInterface;

    public function getContainer(): ?string;
}
