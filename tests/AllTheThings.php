<?php

namespace Mustache\Tests;

use ArrayAccess;

class AllTheThings implements ArrayAccess
{
    public string $foo  = 'fail';
    public string $bar  = 'win';

    public function foo(): string
    {
        return 'win';
    }

    public function offsetExists($offset): bool
    {
        return true;
    }

    public function offsetGet($offset): string
    {
        return match ($offset) {
            'foo', 'bar' => 'fail',
            'baz', 'qux' => 'win',
            default => 'lolwhut',
        };
    }

    public function offsetSet($offset, $value): void
    {
        // nada
    }

    public function offsetUnset($offset): void
    {
        // nada
    }
}
