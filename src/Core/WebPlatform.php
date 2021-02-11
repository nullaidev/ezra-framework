<?php
declare(strict_types=1);

namespace Ezra\Framework\Core;

class WebPlatform
{
    public function __construct(
        protected string $root,
        protected Config $config,
        protected Container $container,
        protected ReflectionResolver $resolver,
        protected Hook $hook,
    ) {
        $this->resolver->setContainer($this->container);
    }

    public function run()
    {

    }
}