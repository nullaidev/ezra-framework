<?php
namespace Ezra\Framework\Core;

class ReflectionResolver
{
    protected ?Container $container = null;

    /**
     * Set Container
     *
     * @param Container $container Platform's container.
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve Class
     *
     * @param string $class Class name to resolve by reflection resolver.
     * @param null|array $args Arguments used by the classes constructor.
     *
     * @throws \ReflectionException
     */
    public function resolveClass(string $class, ?array $args = null) : object
    {
        if($containerInstance = $this->container?->resolve($class)) {
            return $containerInstance;
        }

        $reflector = new \ReflectionClass($class);
        if(!$reflector->isInstantiable()) {
            throw new \ReflectionException($class . ' is not instantiable');
        }
        if(!$constructor = $reflector->getConstructor()) {
            return new $class;
        }
        $dependencies = $this->getDependencyArgs($constructor->getParameters(), $args);
        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve Callable
     *
     * @param callable $callable Callable to call by reflection resolver.
     * @param null|array $args Arguments used by the callable.
     *
     * @throws \ReflectionException
     */
    public function resolveCallable(callable $callable, ?array $args = null) : mixed
    {
        if(is_array($callable)) {
            $ref = new \ReflectionMethod($callable[0], $callable[1]);
        } else {
            $ref = new \ReflectionFunction($callable);
        }

        $dependencies = $this->getDependencyArgs($ref->getParameters(), $args);

        return call_user_func_array( $callable, $dependencies );
    }

    /**
     * Get Dependencies
     *
     * @param \ReflectionParameter[] $parameters Reflection parameters.
     * @param null|array $args Arguments used by inject as a dependency.
     *
     * @throws \ReflectionException
     */
    protected function getDependencyArgs(array $parameters, ?array $args = null) : array
    {
        $dependencies = [];
        $i = 0;

        foreach ($parameters as $parameter)
        {
            $varName = $parameter->getName();
            $dependency = $parameter->getClass();

            if(isset($args[$varName])) {
                $v = $args[$varName];
            } elseif(isset($args[$i])) {
                $v = $args[$i];
                $i++;
            } else {
                $v = null;
            }

            if (!$dependency) {
                $dependencies[] = $v ?? $this->resolveNonClass($parameter);
            } else {
                $dependencies[] = $v instanceof $dependency->name ? $v : $this->resolve($dependency->name);
            }
        }

        return $dependencies;
    }

    /**
     * Resolve None Class
     *
     * Inject default value.
     *
     * @param \ReflectionParameter $parameter Reflection parameter.
     *
     * @throws \ReflectionException
     */
    protected function resolveNonClass(\ReflectionParameter $parameter) : mixed
    {
        if($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new \ReflectionException('Resolver failed because there is no default value for the parameter: $' . $parameter->getName());
    }
}