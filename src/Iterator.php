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
     * Limit sequence.
     *
     * @param int $limit
     *
     * @return Iterator
     */
    public function limit($limit)
    {
        return new self($this, function () use (&$limit) {
            return --$limit > 0;
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
        return new self($this, function (self $sequence, $key, &$value) use ($callback) {
            $value = $callback($value, $key);
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

        return new self(new self($this, function (self $sequence, $key, &$value) use ($callback) {
            return $callback !== null && !$callback($value, $key);
        }));
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
        return new self($this, function (self $sequence, $key, &$value) use ($callback) {
            while (!$callback($value, $key)) {
                $sequence->next();
                if (!$sequence->iterator->valid()) {
                    break;
                }
                $value = $sequence->iterator->current();
            }
        });
    }

    /**
     * Reduce sequence.
     *
     * @param callable $callback
     * @param mixed    $initial
     *
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        foreach ($this as $value) {
            $initial = $callback($initial, $value, $this->key());
        }

        return $initial;
    }

    /**
     * Get limited sequence as array.
     *
     * @return array
     */
    public function all()
    {
        return $this->reduce(function ($array, $value, $key) {
            $array[$key] = $value;
            return $array;
        }, []);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        if (!$this->terminate && $this->iterator->valid()) {
            $this->value = $this->iterator->current();
            if ($this->handler !== null) {
                $this->terminate = ($this->handler)($this, $this->key(), $this->value) === false;
            }

            return $this->iterator->valid();
        }

        return false;
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
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->iterator->next();
    }
}
