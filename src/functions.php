<?php
/**
 * Get Constant
 *
 * Serves the same purpose as constant() but also provides a
 * default value if the constant is null or not defined.
 *
 * @param string $name The constant variable name.
 * @param mixed $default The default value.
 *
 * @return mixed
 */
function def(string $name, $default = null) : mixed
{
    return constant($name) ?? $default;
}

/**
 * Get Environment Variable
 *
 * @param string $name The environment variable name.
 * @param mixed $default The default value.
 *
 * @return mixed
 */
function env(string $name, $default = null) : mixed
{
    return getenv($name) ?: ($_ENV[$name] ?? $default);
}

/**
 * Die and Dump
 *
 * Dump the variables and exit.
 *
 * @param mixed ...$params Variables to var_dump.
 */
function dd(mixed ...$params) : void
{
    var_dump(...$params);
    exit();
}

/**
 * Get Time Spent Running Since Request
 *
 * @param bool $milliseconds Get the run time in milliseconds.
 */
function time_spent(bool $milliseconds = true) : int
{
    $run = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    if( $milliseconds ) { return $run * 1000; }
    return $run;
}

/**
 * Value Is "blank"
 *
 * This function treats some values differently than you might expect.
 *
 * Not blank: 0
 * Is blank: ' '
 *
 * @param mixed $var The variable being evaluated.
 *
 * @return bool
 */
function is_blank($var) : bool
{
    if (is_null($var)) {
        return true;
    }
    elseif (is_string($var)) {
        return trim($var) === '';
    }
    elseif (is_numeric($var) || is_bool($var)) {
        return false;
    }
    elseif ($var instanceof Countable) {
        return count($var) === 0;
    }

    return empty($var);
}

/**
 * Value Is "filled"
 *
 * This function treats some values differently than you might expect.
 *
 * Is filled: 0
 * Not filled: ' '
 *
 * @param mixed $var The variable being evaluated.
 *
 * @return bool
 */
function is_filled($var) : bool
{
    return !is_blank($var);
}

/**
 * Is Array Accessible
 *
 * @param mixed $var The variable being evaluated.
 *
 * @return bool
 */
function is_array_access($var) : bool
{
    return is_array($var) || $var instanceof ArrayAccess;
}

/**
 * Array Partition
 *
 * Partition and spread array semi-evenly across a number of groups.
 *
 * @param array $array The array being evaluated.
 * @param int $number The number of groups.
 *
 * @return array
 */
function array_partition(array $array, int $number) : array
{
    $count = count( $array );
    $parts = floor( $count / $number );
    $rem = $count % $number;
    $partition = [];
    $mark = 0;
    for ($index = 0; $index < $number; $index++)
    {
        $incr = ($index < $rem) ? $parts + 1 : $parts;
        $partition[$index] = array_slice( $array, $mark, $incr );
        $mark += $incr;
    }
    return $partition;
}

/**
 * Array Get
 *
 * Strictly get a value from an array using dot notation without wilds.
 *
 * @param string|array $needle Value to check in dot notation, or an array of string values.
 * @param array|ArrayAccess $haystack Array to search.
 * @param mixed $default Fallback if value is null.
 *
 * @return mixed
 */
function array_get($needle, array $haystack, $default = null) : mixed
{
    $search = is_array($needle) ? $needle : explode('.', $needle);

    foreach ($search as $index) {
        if(isset($haystack[$index])) {
            $haystack = $haystack[$index];
        }
        else {
            return $default;
        }
    }

    return $haystack ?? $default;
}

/**
 * Array Dot
 *
 * Flatten array dimensions to one level and meld keys into dot notation.
 * Resolves a deeply nested array to ['key.child' => 'value'].
 *
 * @param array $array the array to compress into dot notation
 *
 * @return array
 */
function array_dot(array $array) : array
{
    $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));
    $result = [];
    foreach ($iterator as $value) {
        $keys = [];
        $depth = range(0, $iterator->getDepth());
        foreach ($depth as $step) {
            $keys[] = $iterator->getSubIterator($step)->key();
        }
        $result[ implode('.', $keys) ] = $value;
    }

    return $result;
}

/**
 * Data Get
 *
 * Get data from an array or object using dot notation with wilds.
 *
 * @param string|array $needle Value to check in dot notation, or an array of string values.
 * @param array|object|ArrayAccess $haystack Data to search.
 * @param mixed $default Fallback if value is null.
 *
 * @return mixed
 */
function data_get($needle, $haystack, $default = null) : mixed
{
    if( empty($needle) ) {
        return $haystack ?? $default;
    }

    $search = is_array($needle) ? $needle : explode('.', $needle);

    foreach($search as $i => $index) {
        unset($search[$i]);

        if($index === '*') {
            if(!is_iterable($haystack)) {
                return $default;
            }

            $list = [];

            foreach ($haystack as $stack) {
                $list[] = data_get($search, $stack, $default);
            }

            return $list;
        }

        if(is_array_access($haystack) && isset($haystack[$index])) {
            $haystack = $haystack[$index];
        }
        elseif(is_object($haystack) && isset($haystack->{$index})) {
            $haystack = $haystack->{$index};
        }
        else {
            return $default;
        }
    }

    return $haystack ?? $default;
}

/**
 * Increment
 *
 * @return int
 */
function increment() : int
{
    static $i = 0;

    return $i++;
}

/**
 * Binary to Hex UUID
 *
 * @param int $half Half the length of the string to return.
 *
 * @return string
 * @throws Exception
 */
function uuid(int $half = 16) : string
{
    return bin2hex(random_bytes($half));
}

/**
 * UUID v4
 *
 * Formatted as follows 7544eee6-4ac1-5883-21a6-0759de6e6873
 *
 * @return string
 * @throws Exception
 */
function uuid4() : string
{
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(uuid(16), 4));
}

/**
 * Hash Callable
 *
 * @param callable $callable The callback to be hashed
 *
 * @return string
 */
function hash_callable(callable $callable) : string
{
    if ( is_string( $callable ) ) {
        return $callable;
    }

    if ( $callable instanceof Closure ) {
        $callable = [$callable, ''];
    }

    if ( is_object( $callable[0] ) ) {
        return spl_object_hash( $callable[0] ) . $callable[1];
    }

    return $callable[0] . '::' . $callable[1];
}