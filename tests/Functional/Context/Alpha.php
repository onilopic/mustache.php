<?php

namespace Mustache\Tests\Functional\Context;

use StdClass;

final class Alpha
{
    public object $foo;

    public function __construct()
    {
        $this->foo = new StdClass();
        $this->foo->name = 'Foo';
        $this->foo->number = 1;
    }
}
