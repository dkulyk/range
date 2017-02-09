<?php

/**
 * Sequence range.
 *
 * @param int|float $low
 * @param int|float $high
 * @param int|float $step
 *
 * @return Iterator
 */
function range_range($low, $high, $step = 1)
{
    if ($high > $low) {
        for ($i = $low; $i < $high; $i = $i + $step) {
            yield $i;
        }
    } else {
        for ($i = $low; $i > $high; $i = $i - $step) {
            yield $i;
        }
    }
}

/**
 * Range tail-end recursion sequence.
 *
 * ```
 * function fibonacci(&$value, $a = 1, $b = 2)
 * {
 *   $value = $a + $b;
 *   return function (&$v) use ($value, $b) {
 *     return fibonacci($v, $b, $value);
 *   };
 * }
 * ```
 *
 * @param callable $callable mixed (&$callable,..$args)
 * @param array    ...$args
 *
 * @return Iterator
 */
function range_sequence(callable $callable, ...$args)
{
    while (is_callable($callable)) {
        $callable = $callable($value, ...$args);
        yield $value;
    }
}

/**
 * Range map.
 *
 * @param iterable $range
 * @param callable $callback
 *
 * @return Iterator
 */
function range_map($range, callable $callback)
{
    foreach ($range as $value) {
        yield $callback($value);
    }
}

/**
 * Filter range.
 *
 * @param iterable $range
 * @param callable $callback
 *
 * @return Iterator
 */
function range_filter($range, callable $callback)
{
    foreach ($range as $value) {
        if ($callback($value)) {
            yield $value;
        }
    }
}

/**
 * Reduce range.
 *
 * @param iterable $range
 * @param callable $callback
 * @param null     $initial
 *
 * @return null
 */
function range_reduce($range, callable $callback, $initial = null)
{
    foreach ($range as $value) {
        $initial = $callback($initial, $value);
    }

    return $initial;
}

/**
 * Limit range items.
 *
 * @param iterable $range
 * @param int      $limit
 *
 * @return \Generator
 */
function range_limit($range, $limit)
{
    foreach ($range as $value) {
        if ($limit-- === 0) {
            break;
        }
        yield $value;
    }
}
