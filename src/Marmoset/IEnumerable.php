<?php
/*
 * This file is part of the Marmoset project.
 *
 * (c) Eric Mann <eric@eamann.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EAMann\Marmoset;

interface IEnumerable
{
    /**
     * Iteravely apply a function to all elements of the Enumerable.
     *
     * @param callable $callback
     *
     * @return void
     */
    function forEach(callable $callback);

    /**
     * Map a callback across the entire collection, returning a new Enumerable.
     *
     * @param callable $callback
     *
     * @return IEnumerable
     */
    function map(callable $callback);

    /**
     * Iteravely reduce the elements of the Enumerable to a single value using a callback.
     *
     * @param callable $callback
     * @param mixed $initial
     *
     * @return mixed
     */
    function reduce(callable $callback, mixed $initial);

    /**
     * Filter the internal collection and return a new Enumerable object with the result.
     *
     * @param callable $callback
     * @param bool     [$retain_keys]
     *
     * @return IEnumerable
     */
    function filter(callable $callback, bool $retain_keys = false);

    /**
     * Push an item onto the collection.
     *
     * @param mixed $item
     *
     * @return IEnumerable
     */
    function push(mixed $item);

    /**
     * Pop the last item off the internal collection.
     *
     * @return mixed
     */
    function head();

    /**
     * Get all items off the collection but the current head.
     *
     * @return IEnumerable
     */
    function tail();
}