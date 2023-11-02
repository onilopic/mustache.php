<?php declare(strict_types=1);

namespace Mustache;

use Mustache\Exception\InvalidArgumentException;
use Mustache\Exception\UnknownHelperException;

/**
 * A collection of helpers for a Mustache instance.
 */
class HelperCollection
{
    private array $helpers = [];

    /**
     * Helper Collection constructor.
     *
     * Optionally accepts an array (or Traversable) of `$name => $helper` pairs.
     *
     * @throws InvalidArgumentException if the $helpers argument isn't an array or Traversable
     *
     * @param ?iterable $helpers (default: null)
     */
    public function __construct(iterable $helpers = null)
    {
        if ($helpers === null) {
            return;
        }

        foreach ($helpers as $name => $helper) {
            $this->add($name, $helper);
        }
    }

    /**
     * Magic mutator.
     *
     * @see \Mustache\HelperCollection::add
     *
     * @param string $name
     * @param mixed  $helper
     */
    public function __set(string $name, mixed $helper)
    {
        $this->add($name, $helper);
    }

    /**
     * Add a helper to this collection.
     *
     * @param string $name
     * @param mixed  $helper
     */
    public function add(string $name, mixed $helper): void
    {
        $this->helpers[$name] = $helper;
    }

    /**
     * Magic accessor.
     *
     * @see \Mustache\HelperCollection::get
     *
     * @param string $name
     *
     * @return mixed Helper
     */
    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    /**
     * Get a helper by name.
     *
     * @throws UnknownHelperException If helper does not exist
     *
     * @param string $name
     *
     * @return mixed Helper
     */
    public function get(string $name): mixed
    {
        if (!$this->has($name)) {
            throw new UnknownHelperException($name);
        }

        return $this->helpers[$name];
    }

    /**
     * Magic isset().
     *
     * @see \Mustache\HelperCollection::has
     *
     * @param string $name
     *
     * @return bool True if helper is present
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    /**
     * Check whether a given helper is present in the collection.
     *
     * @param string $name
     *
     * @return bool True if helper is present
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->helpers);
    }

    /**
     * Magic unset().
     *
     * @see \Mustache\HelperCollection::remove
     *
     * @param string $name
     */
    public function __unset(string $name): void
    {
        $this->remove($name);
    }

    /**
     * Check whether a given helper is present in the collection.
     *
     * @param string $name
     *@throws UnknownHelperException if the requested helper is not present
          *
     */
    public function remove(string $name): void
    {
        if (!$this->has($name)) {
            throw new UnknownHelperException($name);
        }

        unset($this->helpers[$name]);
    }

    /**
     * Clear the helper collection.
     *
     * Removes all helpers from this collection
     */
    public function clear(): void
    {
        $this->helpers = array();
    }

    /**
     * Check whether the helper collection is empty.
     *
     * @return bool True if the collection is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->helpers);
    }
}
