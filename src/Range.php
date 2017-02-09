<?php

namespace DKulyk\Sequence;

/**
 * Class Range.
 *
 * @package DKulyk\Sequence
 */
class Range extends Sequence
{
    /**
     * Range constructor.
     *
     * @param int|float $low
     * @param int|float|null     $high
     * @param int|float      $step
     */
    public function __construct($low, $high = null, $step = 1)
    {
        parent::__construct(function (&$value, $low, $high, $step) {
            if ($high === null) {
                return $this->increase($value, $low, $high, $step);
            } elseif ($high >= $low) {
                return $this->increase($value, $low, $high, abs($step));
            } else {
                return $this->decrease($value, $low, $high, abs($step));
            }
        }, $low, $high, $step);
    }

    /**
     * @param $value
     * @param $low
     * @param $high
     * @param $step
     *
     * @return \Closure|null
     */
    protected function increase(&$value, $low, $high, $step)
    {
        $value = $low;
        if ($high !== null && $value > $high) {
            return null;
        }
        return function (&$v) use ($low, $high, $step) {
            return $this->increase($v, $low + $step, $high, $step);
        };
    }

    /**
     * @param $value
     * @param $low
     * @param $high
     * @param $step
     *
     * @return \Closure|null
     */
    protected function decrease(&$value, $low, $high, $step)
    {
        $value = $low;
        if ($high !== null && $value < $high) {
            return null;
        }
        return function (&$v) use ($low, $high, $step) {
            return $this->decrease($v, $low - $step, $high, $step);
        };
    }
}
