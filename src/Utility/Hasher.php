<?php
namespace Ezra\Framework\Utility;

/**
 * Hasher
 *
 * A common sense API for hashing.
 */
class Hasher
{
    /**
     * Hash Callable
     *
     * @param callable $callable The callback to be hashed.
     */
    public static function hashCallable(callable $callable) : string
    {
        if ( is_string( $callable ) ) {
            return $callable;
        }

        if ( $callable instanceof \Closure ) {
            $callable = [$callable, ''];
        }

        if ( is_object( $callable[0] ) ) {
            return spl_object_hash( $callable[0] ) . $callable[1];
        }

        return $callable[0] . '::' . $callable[1];
    }
}