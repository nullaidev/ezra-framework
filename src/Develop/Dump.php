<?php
namespace Ezra\Framework\Develop;

/**
 * Dump
 *
 * A class that makes developing in PHP more robust.
 */
class Dump
{
    /**
     * Die and Dump
     *
     * Dump the variables and exit.
     *
     * @param mixed ...$params Variables to var_dump.
     */
    public static function die(mixed ...$params) : void
    {
        var_dump(...$params);
        exit();
    }
}