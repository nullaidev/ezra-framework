<?php
declare(strict_types=1);

namespace Ezra\Framework\Core;

class Platform
{
    public function __construct(protected string $root)
    {
        $this->root = realpath($root);

        date_default_timezone_set('UTC');
        mb_internal_encoding('UTF-8');
        error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
    }
}