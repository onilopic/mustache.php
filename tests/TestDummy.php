<?php

namespace Mustache\Tests;

class TestDummy
{
    public string $name = 'dummy';

    public function __invoke(): void
    {
        // nothing
    }

    public static function foo(): string
    {
        return '<foo>';
    }

    public function bar(): string
    {
        return '<bar>';
    }
}