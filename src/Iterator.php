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
     * @var mixed
     */
    protected $key;

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
                $this->key = $this->iterator->key();
                $this->value = $this->iterator->current();
                return true;
            }
            return false;
        };

        if ($this->valid()) {
            if ($this->handler !== null) {
                $this->terminate = ($this->handler)($next, $this) === false;
            } else {
                $next();
            }
        }
    }

    /**
     * Get a new iterator with the specified handler.
     * ```
     *      //Iterated until the value is not equal to 3
     *      (new Iterator(new ArrayIterator([1,2,3,4,5])))
     *          ->handle(function (callable $next, Iterator $sequence) {
     *              return $next() && $sequence->current() !== 3;
     *          })
     *          ->all()
     * ```
     *
     * @param callable $callback Return false for end sequence
     *
     * @return Iterator
     */
    public function handle(callable $callback)
    {
        return new self($this, $callback);
    }

    /**
     * Sequence length limit.
     *
     * @param int $limit
     *
     * @return Iterator
     */
    public function limit($limit)
    {
        return $this->handle(function (callable $next) use (&$limit) {
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
        return $this->handle(function (callable $next, self $sequence) use ($callback) {
            if ($next()) {
                $sequence->setValue($callback($sequence->current(), $sequence->key()));
                return true;
            }
            return false;
        });
    }

    /**
     * Ending sequence with the condition.
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

        return $this->handle(function (callable $next, self $sequence) use ($callback) {
            return $next() && !$callback($sequence->current(), $sequence->key());
        });
    }

    /**
     * Sequence filtering.
     *
     * @param callable $callback
     *
     * @return Iterator
     */
    public function filter(callable $callback)
    {
        return $this->handle(function (callable $next, self $sequence) use ($callback) {
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
        return $this->key;
    }

    /**
     * Replace current key.
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Replace current value.
     *
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }
}
