<?php

namespace Mustache\Tests;


class AllTheThings implements \ArrayAccess
{
    public $foo  = 'fail';
    public $bar  = 'win';
    private $qux = 'fail';

    public function foo()
    {
        return 'win';
    }

    public function offsetExists($offset)
    {
        return true;
    }

    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'foo':
            case 'bar':
                return 'fail';

            case 'baz':
            case 'qux':
                return 'win';

            default:
                return 'lolwhut';
        }
    }

    public function offsetSet($offset, $value)
    {
        // nada
    }

    public function offsetUnset($offset)
    {
        // nada
    }
}