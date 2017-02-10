<?php

namespace DKulyk\Sequence;

/**
 * Class Sequence
 *
 * @package DKulyk
 */
class Sequence extends Iterator
{
    /**
     * @var callable
     */
    protected $sequence;

    /**
     * @var callable
     */
    protected $initial;

    /**
     * @var int
     */
    protected $key = 0;

    /**
     * Sequence constructor.
     *
     * @param callable $sequence
     * @param array    ...$args
     */
    public function __construct(callable $sequence, ...$args)
    {
        $this->initial = function (&$value) use ($args, $sequence) {
            return $sequence($value, ...$args);
        };
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->sequence = ($this->initial)($this->value);
        $this->key = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return !$this->terminate && is_callable($this->sequence);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->sequence = ($this->sequence)($this->value);
        ++$this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->key;
    }
}
