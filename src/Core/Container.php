<?php
namespace Ezra\Framework\Core;

/**
 * Class Container
 *
 * Inversion of control container.
 */
class Container
{
    protected array $list = [];

    /**
     * Resolve Class
     *
     * Resolve a class from the container by its class name or an alias. An alias
     * can be referenced by including an @ followed by the alias name.
     *
     * @param string $id Class name or alias to resolve instance from container.
     */
    public function resolve(string $id) : ?object
    {
        if(isset($this->list[$id]))
        {
            $registered =& $this->list[$id];
            $instance = $registered->singletonInstance ?? call_user_func($registered->callback);

            if(1 === $registered->singletonMode)
            {
                $registered->singletonMode += 2;
                $registered->singletonInstance = $instance;
            }

            return $instance;
        }

        return null;
    }

    /**
     * Register Class
     *
     * @param string $className Class name to register.
     * @param callable $callback Registration callback.
     * @param bool $singleton Make container instance as singleton.
     * @param null|string $alias Alias name for quick lookup without an @.
     *
     * @throws \Exception
     */
    public function register(string $className, callable $callback, bool $singleton = false, ?string $alias = null) : bool
    {
        if(!class_exists($className)) {
            throw new \Exception('Failed to register class to container because the class does not exist.');
        }

        if(!empty($this->list[$className])) {
            return false;
        }

        $obj = new \stdClass();
        $obj->callback = $callback;
        $obj->singletonMode = $singleton ? 1 : 0;
        $obj->singletonInstance = null;

        $this->list[$className] = $obj;

        if($alias) {
            $this->list['@'.$alias] = $obj;
        }

        return true;
    }
}