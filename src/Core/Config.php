<?php
namespace Ezra\Framework\Core;

class Config
{
    public function __construct(
        protected string $root,
    ) {}
}