# Allure PHP API

[![Build](https://github.com/allure-framework/allure-php-api/actions/workflows/build.yml/badge.svg)](https://github.com/allure-framework/allure-php-api/actions/workflows/build.yml)

This repository contains PHP API for Allure framework. The main idea is to reuse this API when creating adapters for different test frameworks.

## Getting started
In order to use this API you simply need to add the following to **composer.json**:
```json
{
    "require": {
        "php": ">=5.4.0",
        "allure-framework/allure-php-api": "~1.0.0"
    }
}
```
Basic usage idiom is to fire an event like the following:
```php
Allure::lifecycle()->fire(new TestCaseFinishedEvent());
```

## Events
The following events are available right now:
* AddAttachmentEvent
* AddParameterEvent
* ClearStepStorageEvent
* ClearTestCaseStorageEvent
* RemoveAttachmentsEvent
* StepCanceledEvent
* StepEvent
* StepFailedEvent
* StepFinishedEvent
* StepStartedEvent
* TestCaseBrokenEvent
* TestCaseCanceledEvent
* TestCaseEvent
* TestCaseFailedEvent
* TestCaseFinishedEvent
* TestCasePendingEvent
* TestCaseStartedEvent
* TestCaseStatusChangedEvent
* TestSuiteEvent
* TestSuiteFinishedEvent
* TestSuiteStartedEvent

## Usage examples
See [allure-phpunit](https://github.com/allure-framework/allure-phpunit) project.
