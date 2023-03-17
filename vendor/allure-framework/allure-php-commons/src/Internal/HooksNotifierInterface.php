<?php

declare(strict_types=1);

namespace Qameta\Allure\Internal;

use Qameta\Allure\Hook\AfterAttachmentWriteHookInterface;
use Qameta\Allure\Hook\AfterContainerStartHookInterface;
use Qameta\Allure\Hook\AfterContainerStopHookInterface;
use Qameta\Allure\Hook\AfterContainerUpdateHookInterface;
use Qameta\Allure\Hook\AfterContainerWriteHookInterface;
use Qameta\Allure\Hook\AfterFixtureStartHookInterface;
use Qameta\Allure\Hook\AfterFixtureStopHookInterface;
use Qameta\Allure\Hook\AfterFixtureUpdateHookInterface;
use Qameta\Allure\Hook\AfterStepStartHookInterface;
use Qameta\Allure\Hook\AfterStepStopHookInterface;
use Qameta\Allure\Hook\AfterStepUpdateHookInterface;
use Qameta\Allure\Hook\AfterTestScheduleHookInterface;
use Qameta\Allure\Hook\AfterTestStartHookInterface;
use Qameta\Allure\Hook\AfterTestStopHookInterface;
use Qameta\Allure\Hook\AfterTestUpdateHookInterface;
use Qameta\Allure\Hook\AfterTestWriteHookInterface;
use Qameta\Allure\Hook\BeforeAttachmentWriteHookInterface;
use Qameta\Allure\Hook\BeforeContainerStartHookInterface;
use Qameta\Allure\Hook\BeforeContainerStopHookInterface;
use Qameta\Allure\Hook\BeforeContainerUpdateHookInterface;
use Qameta\Allure\Hook\BeforeContainerWriteHookInterface;
use Qameta\Allure\Hook\BeforeFixtureStartHookInterface;
use Qameta\Allure\Hook\BeforeFixtureStopHookInterface;
use Qameta\Allure\Hook\BeforeFixtureUpdateHookInterface;
use Qameta\Allure\Hook\BeforeStepStartHookInterface;
use Qameta\Allure\Hook\BeforeStepStopHookInterface;
use Qameta\Allure\Hook\BeforeStepUpdateHookInterface;
use Qameta\Allure\Hook\BeforeTestScheduleHookInterface;
use Qameta\Allure\Hook\BeforeTestStartHookInterface;
use Qameta\Allure\Hook\BeforeTestStopHookInterface;
use Qameta\Allure\Hook\BeforeTestUpdateHookInterface;
use Qameta\Allure\Hook\BeforeTestWriteHookInterface;
use Qameta\Allure\Hook\OnLifecycleErrorHookInterface;

interface HooksNotifierInterface extends
    BeforeContainerStartHookInterface,
    AfterContainerStartHookInterface,
    BeforeContainerUpdateHookInterface,
    AfterContainerUpdateHookInterface,
    BeforeContainerStopHookInterface,
    AfterContainerStopHookInterface,
    BeforeContainerWriteHookInterface,
    AfterContainerWriteHookInterface,
    BeforeFixtureStartHookInterface,
    AfterFixtureStartHookInterface,
    BeforeFixtureUpdateHookInterface,
    AfterFixtureUpdateHookInterface,
    BeforeFixtureStopHookInterface,
    AfterFixtureStopHookInterface,
    BeforeTestScheduleHookInterface,
    AfterTestScheduleHookInterface,
    BeforeTestStartHookInterface,
    AfterTestStartHookInterface,
    BeforeTestUpdateHookInterface,
    AfterTestUpdateHookInterface,
    BeforeTestStopHookInterface,
    AfterTestStopHookInterface,
    BeforeTestWriteHookInterface,
    AfterTestWriteHookInterface,
    BeforeStepStartHookInterface,
    AfterStepStartHookInterface,
    BeforeStepUpdateHookInterface,
    AfterStepUpdateHookInterface,
    BeforeStepStopHookInterface,
    AfterStepStopHookInterface,
    BeforeAttachmentWriteHookInterface,
    AfterAttachmentWriteHookInterface,
    OnLifecycleErrorHookInterface
{
}
