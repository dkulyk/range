<?php

namespace DKulyk\Sequence;

/**
 * Class Iterator
 *
 * @package DKulyk\Sequence
 */
class Iterator implements \Iterator
{
    /**
     * @var \Iterator
     */
    private $iterator;

    /**
     * @var callable|null
     */
    private $handler;

    /**
     * @var bool
     */
    protected $terminate = false;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * Iterator constructor.
     *
     * @param \Iterator     $iterator
     * @param callable|null $handler
     */
    public function __construct(\Iterator $iterator, callable $handler = null)
    {
        $this->iterator = $iterator;
        $this->handler = $handler;
    }

    /**
     * Fetch current value from iterator.
     *
     * @param bool $first
     */
    private function fetch($first = false)
    {
        $next = function () use (&$first) {
            if ($first) {
                $first = false;
            } else {
                $this->iterator->next();
            }
            if ($this->valid()) {
                $this->value = $this->iterator->current();
                return true;
            }
            return false;
        };

        if ($this->valid()) {
            if ($this->handler !== null) {
                $this->terminate = ($this->handler)($next, $this) === false;
            } else {
                $this->terminate = $next();
            }
        }
    }

    /**
     * Limit sequence.
     *
     * @param int $limit
     *
     * @return Iterator
     */
    public function limit($limit)
    {
        return new self($this, function (callable $next) use (&$limit) {
            if (--$limit >= 0) {
                return $next();
            }
            return false;
        });
    }

    /**
     * Map sequence.
     *
     * @param callable $callback
     *
     * @return Iterator
     */
    public function map(callable $callback)
    {
        return new self($this, function (callable $next, self $sequence) use ($callback) {
            if ($next()) {
                $sequence->value = $callback($sequence->current(), $sequence->key());
                return true;
            }
            return false;
        });
    }

    /**
     * Terminate sequence.
     *
     * @param callable|null $callback
     *
     * @return Iterator
     */
    public function terminate(callable $callback = null)
    {
        if ($callback === null) {
            $this->terminate = true;
            return $this;
        }

        return new self($this, function (callable $next, self $sequence) use ($callback) {
            return $next() && !$callback($sequence->current(), $sequence->key());
        });
    }

    /**
     * Filter sequence.
     *
     * @param callable $callback
     *
     * @return Iterator
     */
    public function filter(callable $callback)
    {
        return new self($this, function (callable $next, self $sequence) use ($callback) {
            while ($next() && !$callback($sequence->current(), $sequence->key())) {
            }
        });
    }

    /**
     * Reduce limited sequence.
     *
     * @param callable $callback
     * @param mixed    $initial
     *
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        foreach ($this as $key => $value) {
            $initial = $callback($initial, $value, $key);
        }

        return $initial;
    }

    /**
     * Get limited sequence as array.
     *
     * @param bool $use_keys [optional] <p>
     * Whether to use the iterator element keys as index.
     * </p>
     * @return array
     */
    public function all($use_keys = true)
    {
        return iterator_to_array($this, $use_keys);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return !$this->terminate && $this->iterator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->fetch(false);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->iterator->rewind();
        $this->fetch(true);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->iterator->key();
    }
}
