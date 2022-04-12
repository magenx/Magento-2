<?php

class ArrayTest extends PHPUnit_Framework_TestCase {
    public function array_has_provider() {
        return [
            [true, ['foo' => 'bar'], 'foo'],
            [false, ['foo' => 'bar'], 'bar'],
            [true, ['foo' => ['bar' => 'baz']], 'foo.bar'],
            [false, ['foo' => ['bar' => 'baz']], 'foo.baz'],
            [true, ['foo' => ['bar' => ['baz' => 'yolo']]], 'foo.bar.baz'],
            [false, ['foo' => ['bar' => ['baz' => 'yolo']]], 'foo.bar.yolo'],
        ];
    }

    public function array_get_provider() {
        return [
            [null, ['foo' => 'bar'], null, null],
            ['bar', ['foo' => 'bar'], 'foo', null],
            ['foo', ['foo' => 'bar'], 'bar', 'foo'],
            ['baz', ['foo' => ['bar' => 'baz']], 'foo.bar', null],
            ['bar', ['foo' => ['bar' => 'baz']], 'foo.baz', 'bar'],
            ['yolo', ['foo' => ['bar' => ['baz' => 'yolo']]], 'foo.bar.baz', null],
            ['baz', ['foo' => ['bar' => ['baz' => 'yolo']]], 'foo.bar.yolo', 'baz'],
        ];
    }

    public function array_set_provider() {
        return [
            [null, null, 'foo'],
            ['foo', 'foo', 'foo'],
            ['bar', 'foo.bar', 'bar'],
            ['baz', 'foo.bar.baz', 'baz'],
        ];
    }

    public function array_remove_provider() {
        return [
            ['foo'],
            ['foo.bar'],
            ['foo.bar.baz']
        ];
    }

    public function array_dot_provider() {
        return [
            [['foo' => 'bar'], ['foo' => 'bar']],
            [['foo.bar' => 'baz'], ['foo' => ['bar' => 'baz']]],
            [['foo.bar.baz' => 'yolo'], ['foo' => ['bar' => ['baz' => 'yolo']]]],
        ];
    }

    public function array_extend_provider() {
        return [
            [
                ['foo' => 'bar', 'bar' => 'bar', [1, 2, 3]],
                ['foo' => 'foo', [1, 2, 3]],
                ['foo' => 'bar', 'bar' => 'bar']
            ],
            [
                ['foo' => ['bar' => 'baz'], [1, 2, 3, 'foo' => 'bar', 'yolo' => 'swag']],
                ['foo' => ['bar' => ['baz' => 'yolo']], [1, 'yolo' => 'swag']],
                ['foo' => ['bar' => 'baz'], [1, 2, 3, 'foo' => 'bar']],
            ],
            [
                [0 => 'yolo', 1 => 'bar', 'bar' => ['bar' => ['baz' => 'swag']], 'baz' => ['foo' => 'bar'], 2 => [1, 3]],
                [0 => 'foo', 1 => 'bar', 'baz' => ['foo' => 'bar'], 2 => [2, 3]],
                [0 => 'yolo', 'bar' => ['bar' => ['baz' => 'swag']], 2 => [1]],
            ],
        ];
    }

    public function array_extend_distinct_provider() {
        return [
            [
                ['foo' => 'bar', 'bar' => 'bar', [1, 2, 3]],
                ['foo' => 'foo', [1, 2, 3]],
                ['foo' => 'bar', 'bar' => 'bar']
            ],
            [
                ['foo' => ['bar' => 'baz'], [1, 2, 3, 'foo' => 'bar', 'yolo' => 'swag']],
                ['foo' => ['bar' => ['baz' => 'yolo']], [1, 'yolo' => 'swag']],
                ['foo' => ['bar' => 'baz'], [1, 2, 3, 'foo' => 'bar']],
            ],
            [
                [0 => 'yolo', 1 => 'bar', 'bar' => ['bar' => ['baz' => 'swag']], 'baz' => ['foo' => 'bar'], 2 => [1]],
                [0 => 'foo', 1 => 'bar', 'baz' => ['foo' => 'bar'], 2 => [2, 3]],
                [0 => 'yolo', 'bar' => ['bar' => ['baz' => 'swag']], 2 => [1]],
            ],
            [
                ['foo' => ['bar' => [5]]],
                ['foo' => ['bar' => [1, 2, 3,]]],
                ['foo' => ['bar' => [5]]],
            ],
        ];
    }

    public function array_is_associative_provider() {
        return [
            [false, [0, '1', 2]],
            [false, [99 => 0, 5 => 1, 2 => 2]],
            [true, ['foo' => 'bar', 1, 2]],
            [true, ['foo' => 'bar', 'bar' => 'baz']],
            [true, []],
        ];
    }

    public function array_is_index_provider() {
        return [
            [false, [1, 2, 3, 'a' => 'foo']],
            [false, [0 => 3, 'a' => 'foo']],
            [true, [1, 2, 3]],
            [true, [0 => 1, '3' => 2]],
            [true, []],
        ];
    }

    public function array_reset_provider() {
        return [
            [
                [0 => 'foo', 'baz' => 'yolo', 1 => 'bar'],
                [10 => 'foo', 'baz' => 'yolo', '199' => 'bar'],
                false
            ],
            [
                [0 => [10 => 'foo', 'baz' => 'yolo', '199' => 'bar'], 'baz' => 'yolo', 1 => 'bar'],
                [10 => [10 => 'foo', 'baz' => 'yolo', '199' => 'bar'], 'baz' => 'yolo', '199' => 'bar'],
                false,
            ],
            [
                [0 => [0 => 'foo', 'baz' => 'yolo', 1 => 'bar'], 'baz' => 'yolo', 1 => 'bar'],
                [10 => [10 => 'foo', 'baz' => 'yolo', '199' => 'bar'], 'baz' => 'yolo', '199' => 'bar'],
                true,
            ],
        ];
    }

    public function array_add_provider() {
        return [
            [['list' => [1, 2, 3]], ['list' => [1, 2]], 'list', 3,],
            [['value' => [1, 2]], ['value' => 1], 'value', 2,],
            [['nested' => ['value' => [1, 2]]], ['nested' => ['value' => 1]], 'nested.value', 2,],
            [['nested' => ['value' => [1, 2]]], ['nested' => ['value' => [1]]], 'nested.value', 2,],
        ];
    }

    /**
     * @dataProvider array_has_provider
     */
    public function test_array_has($expected, $array, $path) {
        $this->assertEquals($expected, array_has($array, $path));
    }

    /**
     * @dataProvider array_get_provider
     */
    public function test_array_get($expected, $array, $path, $default) {
        $this->assertEquals($expected, array_get($array, $path, $default));
    }

    /**
     * @dataProvider array_set_provider
     */
    public function test_array_set($expected, $path, $value) {
        $array = [];
        $this->assertFalse(array_has($array, $path));
        array_set($array, $path, $value);
        $this->assertEquals($expected, array_get($array, $path));
    }

    /**
     * @dataProvider array_remove_provider
     */
    public function test_array_remove($path) {
        $array = [];
        array_set($array, $path, 'foo');
        $this->assertTrue(array_has($array, $path));
        array_remove($array, $path);
        $this->assertFalse(array_has($array, $path));
    }

    /**
     * @dataProvider array_dot_provider
     */
    public function test_array_dot($expected, $array) {
        $this->assertEquals($expected, array_dot($array));
    }

    /**
     * @dataProvider array_extend_provider
     */
    public function test_array_extend($expected, $array1, $array2) {
        $this->assertEquals($expected, array_extend($array1, $array2));
    }

    public function test_array_extend_many() {
        $expected = [
            'foo' => 'bar', 'bar' => 'foo', 'baz' => 'foo', 'yolo' => 'swag'
        ];

        $array1 = ['foo' => 'bar'];
        $array2 = ['bar' => 'foo'];
        $array3 = ['baz' => 'foo'];
        $array4 = ['yolo' => 'swag'];

        $this->assertEquals($expected, array_extend($array1, $array2, $array3, $array4));
    }

    /**
     * @dataProvider array_extend_distinct_provider
     */
    public function test_array_extend_distinct($expected, $array1, $array2) {
        $this->assertEquals($expected, array_extend_distinct($array1, $array2));
    }

    /**
     * @dataProvider array_is_associative_provider
     */
    public function test_array_is_associative($expected, $array) {
        $this->assertEquals($expected, array_is_associative($array));
    }

    /**
     * @dataProvider array_is_index_provider
     */
    public function test_array_is_indexed($expected, $array) {
        $this->assertEquals($expected, array_is_indexed($array));
    }

    /**
     * @dataProvider array_reset_provider
     */
    public function test_array_reset($expected, $array, $deep) {
        $this->assertEquals($expected, array_reset($array, $deep), $deep);
    }

    /**
     * @dataProvider array_add_provider
     */
    public function test_array_add($expected, $array, $key, $value) {
        $this->assertEquals($expected, array_add($array, $key, $value));
    }

    public function test_array_take() {
        $array = ['foo' => ['bar' => 'baz']];
        $this->assertEquals('baz', array_take($array, 'foo.bar'));
        $this->assertEquals(['foo' => []], $array);
    }

    public function test_array_first() {
        $array = ['foo', 'bar', 'baz'];
        $this->assertEquals('foo', array_first($array));
    }

    public function test_array_first_returns_default_value() {
        $this->assertEquals('foo', array_first([], 'foo'));
    }

    public function test_array_last() {
        $array = ['foo', 'bat', 'baz'];
        $this->assertEquals('baz', array_last($array));
    }

    public function test_array_last_returns_default_value() {
        $this->assertEquals('baz', array_last([], 'baz'));
    }

    public function test_array_contains() {
        $this->assertTrue(array_contains(['foo', 'bar'], 'bar'));
        $this->assertFalse(array_contains(['foo', 'bar'], true));
        $this->assertFalse(array_contains([true, 'bar'], 'foo'));
        $this->assertTrue(array_contains([true, 'bar'], true));
        $this->assertFalse(array_contains([true, 'bar'], 'true'));
    }
}
