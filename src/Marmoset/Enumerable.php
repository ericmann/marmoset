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

class Enumerable implements IEnumerable, \ArrayAccess, \Iterator
{
    private $collection = [];

    public function __construct($array)
    {
        $this->collection = $array;
    }

    /**
     * Iteravely apply a function to all elements of the Enumerable.
     *
     * @param callable $callback
     *
     * @return void
     */
    public function forEach(callable $callback)
    {
        foreach($this->collection as $key => $value) {
            $callback($value);
        }
    }

    /**
     * Map a callback across the entire collection, returning a new Enumerable.
     *
     * @param callable $callback
     *
     * @return IEnumerable
     */
    public function map(callable $callback)
    {
        $result = [];
        foreach($this->collection as $key => $value) {
            $result[] = $callback($value);
        }
        return new self($result);
    }

    /**
     * Iteravely reduce the elements of the Enumerable to a single value using a callback.
     *
     * @param callable $callback
     * @param mixed $initial
     *
     * @return mixed
     */
    public function reduce(callable $callback, mixed $initial)
    {
        $final = $initial;
        foreach ($this->collection as $key => $value) {
            $final = $callback($value, $final);
        }
        return $final;
    }

    /**
     * Filter the internal collection and return a new Enumerable object with the result.
     *
     * @param callable $callback
     * @param bool     [$retain_keys]
     *
     * @return IEnumerable
     */
    public function filter(callable $callback, bool $retain_keys = false)
    {
        $result = [];
        foreach($this->collection as $key => $value) {
            if ($callback($value)){
                if ($retain_keys) {
                    $result[$key] = $value;
                } else {
                    $result[] = $value;
                }
            }
        }
        return new self($result);
    }

    /**
     * Push an item onto the collection.
     *
     * @param mixed $item
     *
     * @return IEnumerable
     */
    public function push(mixed $item)
    {
        $new_collection = $this->collection;
        $new_collection[] = $item;
        return new self($new_collection);
    }

    /**
     * Get the first item off the internal collection.
     *
     * @return mixed
     */
    public function head()
    {
        $new_collection = $this->collection;

        return array_pop(&$new_collection);
    }

    /**
     * Get all items off the collection but the current head.
     *
     * @return IEnumerable
     */
    public function tail()
    {
        $new_collection = $this->collection;
        array_pop(&$new_collection);

        return $new_collection;
    }

    /**
     * Test whether or not the internal collection contains a key.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }

    /**
     * Get the value stored in the internal collection for a given key.
     *
     * @param mixed $offset
     *
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->collection[$offset]) ? $this->collection[$offset] : null;
    }

    /**
     * Store a value for a given key into the collection. Overwrite any existing values.
     *
     * @param mixed $offset
     *
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->collection[] = $value;
        } else {
            $this->collection[$offset] = $value;
        }
    }

    /**
     * Remove a value from the internal collection.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }

    /**
     * Current position of the array.
     *
     * @link http://php.net/manual/en/iterator.current.php
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * Key of the current element.
     *
     * @link http://php.net/manual/en/iterator.key.php
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->collection);
    }

    /**
     * Move the internal point of the container array to the next item
     *
     * @link http://php.net/manual/en/iterator.next.php
     *
     * @return void
     */
    public function next()
    {
        next($this->collection);
    }

    /**
     * Rewind the internal point of the container array.
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->collection);
    }

    /**
     * Is the current key valid?
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     *
     * @return bool
     */
    public function valid()
    {
        return $this->offsetExists($this->key());
    }
}