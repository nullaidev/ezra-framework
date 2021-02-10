<?php
namespace Ezra\Framework\Utility;

/**
 * Uuid
 *
 * A common sense API for making UUIDs.
 */
class Uuid
{
    /**
     * Binary to Hex UUID
     *
     * @param int $half Half the length of the string to return.
     *
     * @throws \Exception
     */
    public static function uuid(int $half = 16) : string
    {
        return bin2hex(random_bytes($half));
    }

    /**
     * UUID v4
     *
     * Formatted as follows: '7544eee6-4ac1-5883-21a6-0759de6e6873'.
     *
     * @throws \Exception
     */
    public static function uuid4() : string
    {
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(static::uuid(16), 4));
    }
}