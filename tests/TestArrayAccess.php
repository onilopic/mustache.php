<?php

namespace Mustache\Tests;

use ArrayAccess;

class TestArrayAccess implements ArrayAccess
{
    private array $container = [];

    public function __construct($array)
    {
        foreach ($array as $key => $value) {
            $this->container[$key] = $value;
        }
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset) : mixed
    {
        return $this->container[$offset] ?? null;
    }
}