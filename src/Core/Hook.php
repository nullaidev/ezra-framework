<?php
namespace Ezra\Framework\Core;

use Ezra\Framework\Utility\Hasher;

class Hook
{
    protected array $action = [];
    protected array $filter = [];

    /**
     * @param string $hook Hook name.
     */
    public function hasFilter(string $hook) : bool
    {
        return isset($this->filter[$hook]);
    }

    /**
     * @param string $hook Hook name.
     */
    public function hasAction(string $hook) : bool
    {
        return isset($this->action[$hook]);
    }

    /**
     * @param string $hook Hook name.
     * @param mixed ...$args Argument(s) passed to callable.
     */
    public function callAction(string $hook, mixed ...$args) : bool
    {
        if($this->hasAction($hook)) {
            foreach ($this->action[$hook] as $priority => $stack) {
                /** @var HookItem[] $stack */
                foreach ($stack as $item) {
                    $call = $item->callable;
                    $call(...array_slice($args, 0, $item->numArgs < 0 ? null : $item->numArgs ));
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $hook Hook name.
     * @param mixed $value The filtered return value.
     * @param mixed ...$args Argument(s) passed to callable.
     */
    public function callFilter(string $hook, mixed $value, mixed ...$args) : mixed
    {
        if($this->hasFilter($hook)) {
            foreach ($this->filter[$hook] as $priority => $stack) {
                /** @var HookItem[] $stack */
                foreach ($stack as $item) {
                    $call = $item->callable;
                    $value = $call($value, ...array_slice($args, 0, $item->numArgs < 0 ? null : $item->numArgs ));
                }
            }
        }

        return $value;
    }

    /**
     * @param string $type Value 'action' or 'filter'.
     * @param string $hook Hook name.
     * @param callable $callable The callback to be run when the filter is applied.
     * @param int $priority The order in which the functions associated with a particular action
     *                      are executed. Lower numbers correspond with earlier execution,
     *                      and functions with the same priority are executed in the order
     *                      in which they were added to the action.
     * @param int|null $numArgs The number of args the callback accepts.
     */
    public function add(string $type, string $hook, callable $callable, int $priority = 10, ?int $numArgs = null) : static
    {
        $hash = Hasher::hashCallable($callable);

        $this->{$type}[$hook][$priority][] = new HookItem(hash: $hash, numArgs: $numArgs, callable: $callable);

        if ( ! isset($this->{$type}[$hook][$priority]) && count( $this->{$type}[$hook] ) > 1 ) {
            ksort( $this->{$type}[$hook], SORT_NUMERIC );
        }

        return $this;
    }

    /**
     * @param string $hook Hook name.
     * @param callable $callable The callback to be run when the filter is applied.
     * @param int $priority The priority.
     * @param int|null $numArgs The number of args the callback accepts.
     */
    public function addAction(string $hook, callable $callable, int $priority = 10, ?int $numArgs = null) : static
    {
        return $this->add('action', ...func_get_args());
    }

    /**
     * @param string $hook Hook name.
     * @param callable $callable The callback to be run when the filter is applied.
     * @param int $priority The priority.
     * @param int|null $numArgs The number of args the callback accepts.
     */
    public function addFilter(string $hook, callable $callable, int $priority = 10, ?int $numArgs = null) : static
    {
        return $this->add('filter', ...func_get_args());
    }

    /**
     * @param string $type Value 'action' or 'filter'.
     * @param string $hook Hook name.
     * @param callable $callable The callback to be removed.
     * @param int $priority The priority to seek.
     */
    public function remove(string $type, string $hook, callable $callable, int $priority = 10) : bool
    {
        if ( ! isset($this->{$type}[$hook][$priority]) ) {
            return false;
        }

        $hash = Hasher::hashCallable($callable);

        /** @var HookItem $item */
        foreach ($this->{$type}[$hook][$priority] as $index => $item) {
            if($item->hash == $hash) {
                unset($this->{$type}[$hook][$priority][$index]);
            }
        }

        return true;
    }

    /**
     * @param string $hook Hook name.
     * @param callable $callable The callback to be removed.
     * @param int $priority The priority to seek.
     */
    public function removeAction(string $hook, callable $callable, int $priority = 10) : bool
    {
        return $this->remove('action', ...func_get_args());
    }

    /**
     * @param string $hook Hook name.
     * @param callable $callable The callback to be removed.
     * @param int $priority The priority to seek.
     */
    public function removeFilter(string $hook, callable $callable, int $priority = 10) : bool
    {
        return $this->remove('filter', ...func_get_args());
    }
}