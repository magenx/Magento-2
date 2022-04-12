# Array helpers

[![Build Status](https://img.shields.io/travis/weew/helpers-array.svg)](https://travis-ci.org/weew/helpers-array)
[![Test Coverage](https://img.shields.io/coveralls/weew/helpers-array.svg)](https://coveralls.io/github/weew/helpers-array)
[![Version](https://img.shields.io/packagist/v/weew/helpers-array.svg)](https://packagist.org/packages/weew/helpers-array)
[![Licence](https://img.shields.io/packagist/l/weew/helpers-array.svg)](https://packagist.org/packages/weew/helpers-array)

## Table of contents

- [Installation](#installation)
- [Introduction](#introduction)
- [Functions](#functions)
    - [array_get](#array_get)
    - [array_has](#array_has)
    - [array_set](#array_set)
    - [array_remove](#array_remove)
    - [array_add](#array_add)
    - [array_take](#array_take)
    - [array_first](#array_first)
    - [array_last](#array_last)
    - [array_reset](#array_reset)
    - [array_dot](#array_dot)
    - [array_extend](#array_extend)
    - [array_extend_distinct](#array_extend_distinct)
    - [array_is_associative](#array_is_associative)
    - [array_is_indexed](#array_is_indexed)
    - [array_contains](#array_contains)

## Installation

`composer require weew/helpers-array`

## Introduction

This tiny library provides various helper functions to deal with arrays.

## Functions

#### array\_get

Get an item from an array using "dot" notation.

`mixed array_get(array $array, mixed $key [, mixed $default = null])`

#### array\_has

Check if an item exists in an array using "dot" notation.

`bool array_has(array $array, mixed $key)`

#### array\_set

Set an array item to a given value using "dot" notation.

`array array_set(array &$array, mixed $key, mixed $value)`

#### array\_remove

Remove one or many array items from a given array using "dot" notation.

`void array_remove(array &$array, mixed $keys)`

#### array\_add

Add an element to the array at a specific location using the "dot" notation.

`array array_add(array &$array, mixed $key, mixed $value)`

#### array\_take

Get an element and remove it from the array using the "dot" notation.

`array array_take(array &$array, mixed $key, [, mixed $default = null])`

#### array\_first

Get the first element from an array.

`array array_first(array &$array, [, mixed $default = null])`

#### array\_last

Get the last element from an array.

`array array_last(array &$array, [, mixed $default = null])`

#### array\_reset

Reset all numerical indexes of an array (start from zero). Non-numerical indexes will stay untouched.

`array array_reset(array $array [, bool $deep = false])`

#### array\_dot

Flatten a multi-dimensional associative array with dots.

`array array_dot(array $array [, string $prepend = ''])`

#### array\_extend

Extend one array with another.

`array array_extend(array $arrays [, array $...])`

#### array\_extend\_distinct

Extend one array with another. Non associative arrays will not be merged but rather replaced.

`array array_extend_distinct(array $arrays [, array $...])`

#### array\_is\_associative

Check if the given array is associative.

`bool array_is_associative(array $array)`

#### array\_is\_indexed

Check if an array has a numeric index.

`bool array_is_indexed(array $array)`

#### array\_contains

Check if array contains a specific element.

`array array_contains(array $array, mixed $search [, bool $strict = true])`
