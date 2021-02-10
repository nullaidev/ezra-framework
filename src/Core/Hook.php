<?php
namespace Ezra\Framework\Core;

use Ezra\Framework\Utility\Hasher;

class Hook
{
    protected array $actions = [];
    protected array $filters = [];

    /**
     * @param string $type
     * @param string $hook
     */
    public function has(string $type, string $hook) : bool
    {
        if($type !== 'action') {
            $type = 'filter';
        }

        return isset($this->{$type}[$hook]);
    }

    /**
     * @param string $hook name
     * @param mixed ...$args
     */
    public function action(string $hook, mixed ...$args)
    {
        if($this->has('action', $hook)) {
            foreach ($this->actions[$hook] as $priority => $stack) {
                /** @var HookItem[] $stack */
                foreach ($stack as $item) {
                    $item->callable->call(...array_slice($args, 0, $item->numArgs < 0 ? null : $item->numArgs ));
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $hook
     * @param mixed $value
     * @param mixed ...$args
     */
    public function filter(string $hook, mixed $value, mixed ...$args) : mixed
    {
        if($this->has('filter', $hook)) {
            foreach ($this->filters[$hook] as $priority => $stack) {
                /** @var HookItem[] $stack */
                foreach ($stack as $item) {
                    $value = $item->callable->call($value, ...array_slice($args, 0, $item->numArgs < 0 ? null : $item->numArgs ));
                }
            }
        }

        return $value;
    }

    /**
     * @param string $property actions or filters.
     * @param string $hook hook name.
     * @param callable $callable The callback to be run when the filter is applied.
     * @param int $priority The order in which the functions associated with a particular action
     *                      are executed. Lower numbers correspond with earlier execution,
     *                      and functions with the same priority are executed in the order
     *                      in which they were added to the action.
     * @param int|null $numArgs The number of args the callback accepts.
     */
    public function add(string $property, string $hook, callable $callable, int $priority = 10, ?int $numArgs = null) : static
    {
        if($property !== 'actions') {
            $property = 'filters';
        }

        $hash = Hasher::hashCallable($callable);

        $priority_exists = isset( $this->{$type}[$hook][$priority] );

        $this->{$type}[$hook][$priority][] = new HookItem(hash: $hash, numArgs: $numArgs, callable: $callable);

        if ( ! $priority_exists && count( $this->{$type} ) > 1 ) {
            ksort( $this->{$property}[$hook], SORT_NUMERIC );
        }

        return $this;
    }
}