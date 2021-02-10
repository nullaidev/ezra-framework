<?php
namespace Ezra\Framework;

class Hook
{
    protected array $actions = [];
    protected array $filters = [];

    /**
     * @param string $hook
     *
     * @return bool
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
    public function action(string $hook, mixed ...$args) : bool
    {
        if($this->has('action', $hook)) {
            foreach ($hook as $priority => $stack) {
                foreach ($stack as $item) {
                    $item['callable'](...array_slice($args, 0, $item['num_args'] < 0 ? null : $item['num_args'] ));
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
     *
     * @return mixed
     */
    public function filter(string $hook, mixed $value, mixed ...$args) : mixed
    {
        if($this->has('filter', $hook)) {
            foreach ($hook as $priority => $stack) {
                foreach ($stack as $item) {
                    $value = $item['callable']($value, ...array_slice($args, 0, $item['num_args'] < 0 ? null : $item['num_args'] ));
                }
            }
        }

        return $value;
    }

    /**
     * @param string $propery actions or filters.
     * @param string $hook hook name.
     * @param callable $callable The callback to be run when the filter is applied.
     * @param int $priority The order in which the functions associated with a particular action
     *                      are executed. Lower numbers correspond with earlier execution,
     *                      and functions with the same priority are executed in the order
     *                      in which they were added to the action.
     * @param int|null $numArgs The number of args the callback accepts.
     *
     * @return static
     */
    public function add(string $propery = 'actions', string $hook, callable $callable, int $priority = 10, ?int $numArgs = null)
    {
        if($propery !== 'actions') {
            $propery = 'filters';
        }

        $hash = hash_callable($callable);

        $priority_exists = isset( $this->{$type}[$hook][$priority] );

        $this->{$type}[$hook][$priority][] = new HookItem(hash: $hash, numArgs: $numArgs, callable: $callable);

        if ( ! $priority_exists && count( $this->{$type} ) > 1 ) {
            ksort( $this->{$propery}[$hook], SORT_NUMERIC );
        }

        return $this;
    }
}