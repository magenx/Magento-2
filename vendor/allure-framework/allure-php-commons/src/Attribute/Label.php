<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

use Attribute;
use Qameta\Allure\Model;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Label extends AbstractLabel
{
    public const ALLURE_ID = Model\Label::ALLURE_ID;
    public const SUITE = Model\Label::SUITE;
    public const PARENT_SUITE = Model\Label::PARENT_SUITE;
    public const SUB_SUITE = Model\Label::SUB_SUITE;
    public const EPIC = Model\Label::EPIC;
    public const FEATURE = Model\Label::FEATURE;
    public const STORY = Model\Label::STORY;
    public const SEVERITY = Model\Label::SEVERITY;
    public const TAG = Model\Label::TAG;
    public const OWNER = Model\Label::OWNER;
    public const LEAD = Model\Label::LEAD;
    public const HOST = Model\Label::HOST;
    public const THREAD = Model\Label::THREAD;
    public const TEST_METHOD = Model\Label::TEST_METHOD;
    public const TEST_CLASS = Model\Label::TEST_CLASS;
    public const PACKAGE = Model\Label::PACKAGE;
    public const FRAMEWORK = Model\Label::FRAMEWORK;
    public const LANGUAGE = Model\Label::LANGUAGE;
}
