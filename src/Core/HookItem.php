<?php
namespace Ezra\Framework\Core;

class HookItem
{
    public \Closure $callable;

    public function __construct(
        public string $hash,
        public int $numArgs,
        callable $callable,
    ) {
        $this->callable = \Closure::fromCallable($callable);
    }
}