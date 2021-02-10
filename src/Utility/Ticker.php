<?php
namespace Ezra\Framework\Utility;

/**
 * Ticker
 *
 * A common sense API for tricking.
 */
class Ticker
{
    /**
     * Increment
     *
     * Incrementing number in a single process.
     */
    public static function increment() : int
    {
        static $i = 0;

        return $i++;
    }
}