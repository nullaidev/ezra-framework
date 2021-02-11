<?php
namespace Ezra\Framework\Core;

class Container
{
    protected static array $list = [];

    /**
     * Resolve Class
     *
     * Resolve a class from the container by its class name or an alias. An alias
     * can be referenced by including an @ followed by the alias name.
     *
     * @param string $id Class name or alias to resolve instance from container.
     */
    public static function resolve(string $id) : ?object
    {
        if(!empty(self::$list[$id]))
        {
            $registered =& self::$list[$id];
            $instance = $registered->singleton_instance ?? call_user_func($registered->callback);

            if(1 === $registered->singleton_mode)
            {
                $registered->singleton_mode += 2;
                $registered->singleton_instance = $instance;
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
    public static function register(string $className, callable $callback, bool $singleton = false, ?string $alias = null) : bool
    {
        if(!class_exists($className)) {
            throw new \Exception('Failed to register class to container because the class does not exist.');
        }

        if(!empty(self::$list[$className])) {
            return false;
        }

        $obj = new \stdClass();
        $obj->callback = $callback;
        $obj->singleton_mode = $singleton ? 1 : 0;
        $obj->singleton_instance = null;

        self::$list[$className] = $obj;

        if($alias) {
            self::$list['@'.$alias] = $obj;
        }

        return true;
    }
}