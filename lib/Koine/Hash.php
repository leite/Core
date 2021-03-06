<?php

namespace Koine;

use InvalidArgumentException;
use ArrayAccess;
use Iterator;
use Countable;
use Closure;

/**
 * @author Marcelo Jacobus <marcelo.jacobus@gmail.com>
 */
class Hash extends Object implements ArrayAccess, Iterator, Countable
{
    /**
     * @var array
     */
    protected $values = array();

    /**
     * @param array $values    The values to initially set to the Hash
     * @param bool  $recursive Whether to recursively transform arrays into
     *                         Objects
     */
    public function __construct(array $values = array(), $recursive = true)
    {
        if ($recursive) {
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    $values[$key] = $this->create($value, $recursive);
                }
            }
        }

        $this->values = $values;
    }

    /**
     * Converts hash to array
     *
     * @param bool $recursive defaults to true
     *
     * @return array
     */
    public function toArray($recursive = true)
    {
        $values = $this->values;

        if (!$recursive) {
            return $values;
        }

        foreach ($values as $key => $value) {
            if (gettype($value) === 'object') {
                if ($value instanceof self) {
                    $value = $value->toArray($recursive);
                }
            }

            $values[$key] = $value;
        }

        return $values;
    }

    /**
     * ArrayAccess implementation
     */
    public function offsetGet($key, $default = null)
    {
        echo "\n", __METHOD__, "\n";
        return isset($this->values[$key]) ? $this->values[$key] : $default;
    }

    /**
     * ArrayAccess implementation
     */
    public function offsetSet($key, $value)
    {
        echo "\n", __METHOD__, "\n";
        if (is_null($key)) {
            $this->values[] = $value;
        } else {
            $this->values[$key] = $value;
        }

        return $this;
    }

    /**
     * ArrayAccess implementation
     */
    public function offsetExists($key)
    {
        echo "\n", __METHOD__, "\n";
        return isset($this->values[$key]);
    }

    /**
     * ArrayAccess implementation
     */
    public function offsetUnset($key)
    {
        echo "\n", __METHOD__, "\n";
        unset($this->values[$key]);

        return $this;
    }

    /**
     * Iterator implementation
     */
    public function current()
    {
        echo "\n", __METHOD__, "\n";
        $current =  current($this->values);

        var_export($current);

        return $current;
    }

    /**
     * Iterator implementation
     */
    public function next()
    {
        echo "\n", __METHOD__, "\n";
        $next = next($this->values);
        var_export($next);
        return $next;
    }

    /**
     * Iterator implementation
     */
    public function key()
    {
        echo "\n", __METHOD__, "\n";
        $key = key($this->values);
        var_export($key);
        return $key;
    }

    /**
     * Iterator implementation
     */
    public function rewind()
    {
        echo "\n", __METHOD__, "\n";
        reset($this->values);

        return $this;
    }

    /**
     * Iterator implementation
     */
    public function valid()
    {
        echo "\n", __METHOD__, "\n";
        $key = key($this->values);
        var_export($key);

        return ($key !== null && $key !== false);
    }

    /**
     * Get a new Hash without elements that have empty or null values
     *
     * @return Hash
     */
    public function compact()
    {
        return $this->reject(function ($value, $key) {
            return $value === '' || $value === null;
        });
    }

    /**
     * Rejects elements if the given function evaluates to true
     *
     * @param Closure $callable function
     *
     * @return Hash the new hash containing the non rejected elements
     */
    public function reject(Closure $callback)
    {
        $hash = $this->create();

        foreach ($this as $key => $value) {
            if ($callback($value, $key) == false) {
                $hash[$key] = $value;
            }
        }

        return $hash;
    }

    /**
     * Select elements if the given function evaluates to true
     *
     * @param Closure $callable function
     *
     * @return Hash the new hash containing the non rejected elements
     */
    public function select(Closure $callback)
    {
        $hash = $this->create();

        foreach ($this as $key => $value) {
            if ($callback($value, $key) == true) {
                $hash[$key] = $value;
            }
        }

        return $hash;
    }

    /**
     * A factory for Hash
     *
     * @param array $params    the params to create a new object
     * @param bool  $recursive whether or not to recursive change arrays into
     *                         objects
     *
     * @return Hash
     */
    public static function create(array $params = array(), $recursive = true)
    {
        $class = get_called_class();

        return new $class($params, $recursive);
    }

    /**
     * Maps elements into a new Hash
     *
     * @param Closure $callback
     *
     * @return Hash
     */
    public function map(Closure $callback)
    {
        $hash = $this->create();

        $this->each(function ($value, $key) use ($callback, $hash) {
            $hash[] = $callback($value, $key);
        });

        return $hash;
    }

    /**
     * Loop the elements of the Hash
     *
     * @param Closure $callable function
     *
     * @return Hash
     */
    public function each(Closure $callable)
    {
        foreach ($this as $key => $value) {
            $callable($value, $key);
        }

        return $this;
    }

    /**
     * Check if has any element
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }

    /**
     * Get the number of elements
     *
     * @return int
     */
    public function count()
    {
        return count($this->toArray());
    }

    /**
     * Get the array keys
     *
     * @return Hash[String] containing the keys
     */
    public function getKeys()
    {
        return $this->create(array_keys($this->toArray()))->map(
            function ($key) {
                return $key;
            }
        );
    }

    /**
     * Get the array keys
     *
     * @deprecated use getKeys instead
     *
     * @return Hash[String] containing the keys
     */
    public function keys()
    {
        return $this->getKeys();
    }

    /**
     * Check object has given key
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasKey($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Gets teh index and removes it from the object
     *
     * @param string $key
     *
     * @return mixed the element on the given index
     */
    public function delete($key)
    {
        $element = $this[$key];
        $this->offsetUnset($key);

        return $element;
    }

    /**
     * Get the value by the key. Throws exception when key is not set
     *
     * @param string $key
     * @param mixed  $default either value or callable function
     *
     * @return mixed the value for the given key
     *
     * @throws InvalidArgumentException
     */
    public function fetch($key, $default = null)
    {
        if ($this->hasKey($key)) {
            return $this[$key];
        } elseif ($default !== null) {
            if (is_callable($default)) {
                return $default($key);
            }

            return $default;
        }

        throw new InvalidArgumentException("Invalid key '$key'");
    }

    /**
     * Get the values at the given indexes.
     *
     * Both work the same:
     *    <code>
     *      $hash->valuesAt(array('a', 'b'));
     *      $hash->valuesAt('a', 'b');
     *    </code>
     *
     * @param mixed keys
     *
     * @return Hash containing the values at the given keys
     */
    public function valuesAt()
    {
        $args = func_get_args();

        if (is_array($args[0])) {
            $args = $args[0];
        }

        $hash = $this->create();

        foreach ($args as $key) {
            $hash[] = $this[$key];
        }

        return $hash;
    }

    /**
     * Join the values of the object
     *
     * @param string $separator defauts to empty string
     *
     * @return string
     */
    public function join($separator = '')
    {
        return implode($separator, $this->toArray());
    }

    /**
     * Get first element
     *
     * @return mixed
     */
    public function getFirst()
    {
        $array = $this->toArray(false);

        return array_shift($array);
    }

    /**
     * Get first element
     *
     * @deprecated use getFirst instead
     *
     * @return mixed
     */
    public function first()
    {
        return $this->getFirst();
    }

    /**
     * Get the last element
     *
     * @return mixed
     */
    public function getLast()
    {
        $array = $this->toArray(false);

        return array_pop($array);
    }

    /**
     * Get the last element
     *
     * @deprecated use getLast instead
     *
     * @return mixed
     */
    public function last()
    {
        return $this->getLast();
    }

    /**
     * Group elements by the given criteria
     *
     * @param mixed $criteria it can be either a callable function or a string,
     *                        representing a key of an element
     *
     * @return Hash
     */
    public function groupBy($criteria)
    {
        $criteria = $this->factoryCallableCriteria($criteria);
        $groups   = $this->create();

        $this->each(function ($element, $key) use ($groups, $criteria) {
            $groupName  = $criteria($element, $key);
            $elements   = $groups->offsetGet($groupName, array());
            $elements[] = $element;
            $groups[$groupName] = $elements;
        });

        return $groups;
    }

    /**
     * Sort elements by the given criteria
     *
     * @param mixed $criteria it can be either a callable function or a string,
     *                        representing a key of an element
     *
     * @return Hash
     */
    public function sortBy($criteria)
    {
        $criteria = $this->factoryCallableCriteria($criteria);
        $sorted   = $this->create();
        $groups   = $this->groupBy($criteria);

        $criterias = $this->map(function ($element, $key) use ($criteria) {
            return $criteria($element, $key);
        })->toArray();

        sort($criterias);
        $criterias = array_unique($criterias);

        foreach ($criterias as $key) {
            foreach ($groups[$key] as $element) {
                $sorted[] = $element;
            }
        }

        return $sorted;
    }

    /**
     * Tells if the object includes the given element in the first
     * level of the collection. Strict mode. compares type
     *
     * @return bool
     */
    public function hasValue($value)
    {
        return in_array($value, $this->toArray(false), true);
    }

    /**
     * TODO: Write some description
     *
     * @param mixed    $memo     callback or the memo. If no memo is given, it must be
     *                           a callable function
     * @param callable $callback the callback receives $injected as first param
     *                           the element value as second param and the
     *                           elemen key as the third param
     *
     * @return mixed
     */
    public function inject($memo = null, $callback = null)
    {
        if ($this->isCallable($callback)) {
            foreach ($this as $key => $value) {
                $memo = $callback($memo, $value, $key);
            }
        } elseif ($this->isCallable($memo)) {
            return $this->inject(null, $memo);
        } else {
            throw new InvalidArgumentException('No callback was given');
        }

        return $memo;
    }

    /**
     * Get a function that returns something based on an element item
     *
     * @mixed $criteria either a callable function that returns a value or a
     *    string that is an element key
     *
     * @return callable
     */
    private function factoryCallableCriteria($criteria)
    {
        if (!$this->isCallable($criteria)) {
            $criteria = function ($element, $key) use ($criteria) {
                return $element->fetch($criteria);
            };
        }

        return $criteria;
    }

    /**
     * Param is function?
     *
     * @param mixed $callable
     *
     * @return bool
     */
    protected function isCallable($callable)
    {
        return gettype($callable) === 'object';
            // Fails in hhvm
            // && get_class($callable) === 'Closure';
    }

    /**
     * Empties hash
     *
     * @return self
     */
    public function clear()
    {
        foreach ($this->getKeys() as $key) {
            $this->delete($key);
        }

        return $this;
    }

    /**
     * Merges the two hashes and return a new Instance of a hash
     *
     * @param Hash    $other
     * @param Closure $closure function to resolv conflicts
     *
     * @return Hash the merged hash
     */
    public function merge(Hash $other, Closure $closure = null)
    {
        return $this->mergeInto(clone $this, $other, $closure);
    }

    /**
     * Merges the two nested hashes and return a new Instance of a hash
     *
     * @param Hash $other
     *
     * @return Hash the merged hash
     */
    public function deepMerge(Hash $other)
    {
        return $this->deepMergeInto(clone $this, $other);
    }

    /**
     * Modifies the the first hash and return
     *
     * @param Hash    $into
     * @param Hash    $other
     * @param Closure $closure function to resolv conflicts
     *
     * @return Hash the merged hash
     */
    protected function deepMergeInto(Hash $into, Hash $other)
    {
        foreach ($other as $key => $value) {
            if (is_object($value)
                && $value instanceof self
                && $into->hasKey($key)
            ) {
                $value = $this->deepMergeInto($into[$key], $value);
            }

            $into[$key] = $value;
        }

        return $into;
    }

    /**
     * Modifies the the first hash and return
     *
     * @param Hash    $into
     * @param Hash    $other
     * @param Closure $closure function to resolv conflicts
     *
     * @return Hash the merged hash
     */
    protected function mergeInto(Hash $into, Hash $other, Closure $closure = null)
    {
        foreach ($other->toArray() as $key => $value) {
            if ($closure && $this->hasKey($key)) {
                $value = $closure($key, $this[$key], $value);
            }

            $into[$key] = $value;
        }

        return $into;
    }
}
