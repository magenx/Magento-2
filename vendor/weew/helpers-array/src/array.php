<?php

if ( ! function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $default
     *
     * @return mixed
     */
    function array_get(array $array, $key, $default = null) {
        if ($key === null) {
            return null;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if ( ! is_array($array) || ! array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }
}

if ( ! function_exists('array_has')) {
    /**
     * Check if an item exists in an array using "dot" notation.
     *
     * @param  array $array
     * @param  string $key
     *
     * @return bool
     */
    function array_has(array $array, $key) {
        if (empty($array) || $key === null) {
            return false;
        }

        if (array_key_exists($key, $array)) {
            return true;
        }

        foreach (explode('.', $key) as $segment) {
            if ( ! is_array($array) || ! array_key_exists($segment, $array)) {
                return false;
            }

            $array = $array[$segment];
        }

        return true;
    }
}

if ( ! function_exists('array_set')) {
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $value
     *
     * @return array
     */
    function array_set(array &$array, $key, $value) {
        if ($key === null) {
            return null;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if ( ! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}

if ( ! function_exists('array_remove')) {
    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  array $array
     * @param  array|string $keys
     *
     * @return void
     */
    function array_remove(array &$array, $keys) {
        $original = &$array;

        if ( ! is_array($keys)) {
            $keys = [$keys];
        }

        foreach ((array) $keys as $key) {
            $parts = explode('.', $key);

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                }
            }

            unset($array[array_shift($parts)]);

            $array = &$original;
        }
    }
}

if ( ! function_exists('array_add')) {
    /**
     * Add an element to the array at a specific location
     * using the "dot" notation.
     *
     * @param array $array
     * @param $key
     * @param $value
     *
     * @return array
     */
    function array_add(array &$array, $key, $value) {
        $target = array_get($array, $key, []);

        if ( ! is_array($target)) {
            $target = [$target];
        }

        $target[] = $value;
         array_set($array, $key, $target);

        return $array;
    }
}

if ( ! function_exists('array_take')) {
    /**
     * Get an item and remove it from the array.
     *
     * @param array $array
     * @param $key
     * @param null $default
     *
     * @return mixed
     */
    function array_take(array &$array, $key, $default = null) {
        $value = array_get($array, $key, $default);

        if (array_has($array, $key)) {
            array_remove($array, $key);
        }

        return $value;
    }
}

if ( ! function_exists('array_first')) {
    /**
     * @param array $array
     * @param null $default
     *
     * @return mixed
     */
    function array_first(array $array, $default = null) {
        if (empty($array)) {
            return $default;
        }

        return reset($array);
    }
}

if ( ! function_exists('array_last')) {
    /**
     * @param array $array
     * @param null $default
     *
     * @return mixed
     */
    function array_last(array $array, $default = null) {
        if (empty($array)) {
            return $default;
        }

        return array_first(array_reverse($array, true), $default);
    }
}

if ( ! function_exists('array_reset')) {
    /**
     * Reset all numerical indexes of an array (start from zero).
     * Non-numerical indexes will stay untouched. Returns a new array.
     *
     * @param array $array
     * @param bool|false $deep
     *
     * @return array
     */
    function array_reset(array $array, $deep = false) {
        $target = [];

        foreach ($array as $key => $value) {
            if ($deep && is_array($value)) {
                $value = array_reset($value);
            }

            if (is_numeric($key)) {
                $target[] = $value;
            } else {
                $target[$key] = $value;
            }
        }

        return $target;
    }
}

if ( ! function_exists('array_dot')) {
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array $array
     * @param  string $prepend
     *
     * @return array
     */
    function array_dot(array $array, $prepend = '') {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, array_dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
}

if ( ! function_exists('array_extend')) {
    /**
     * Extend one array with another.
     *
     * @param array $arrays
     *
     * @return array
     */
    function array_extend(array $arrays) {
        $merged = [];

        foreach (func_get_args() as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && array_has($merged, $key) && is_array($merged[$key])) {
                    $merged[$key] = array_extend($merged[$key], $value);
                } else {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }
}

if ( ! function_exists('array_extend_distinct')) {
    /**
     * Extend one array with another. Non associative arrays will not be merged
     * but rather replaced.
     *
     * @param array $arrays
     *
     * @return array
     */
    function array_extend_distinct(array $arrays) {
        $merged = [];

        foreach (func_get_args() as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) &&
                    array_has($merged, $key) &&
                    is_array($merged[$key])
                ) {
                    if (array_is_associative($value) && array_is_associative($merged[$key])) {
                        $merged[$key] = array_extend_distinct($merged[$key], $value);

                        continue;
                    }
                }

                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}

if ( ! function_exists('array_is_associative')) {
    /**
     * Check if the given array is associative.
     *
     * @param array $array
     *
     * @return bool
     */
    function array_is_associative(array $array) {
        if ($array == []) {
            return true;
        }

        $keys = array_keys($array);

        if (array_keys($keys) !== $keys) {
            foreach ($keys as $key) {
                if ( ! is_numeric($key)) {
                    return true;
                }
            }
        }

        return false;
    }
}

if ( ! function_exists('array_is_indexed')) {
    /**
     * Check if an array has a numeric index.
     *
     * @param array $array
     *
     * @return bool
     */
    function array_is_indexed(array $array) {
        if ($array == []) {
            return true;
        }

        return ! array_is_associative($array);
    }
}

if ( ! function_exists('array_contains')) {
    /**
     * Check if an array contains a specific value.
     *
     * @param array $array
     * @param $search
     * @param bool $strict
     *
     * @return bool
     */
    function array_contains(array $array, $search, $strict = true) {
        return in_array($search, $array, $strict);
    }
}
