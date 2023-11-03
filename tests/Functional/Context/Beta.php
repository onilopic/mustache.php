<?php

namespace Mustache\Tests\Functional\Context;

use StdClass;

class Beta
{
    protected array $data = [];

    public function __construct()
    {
        $this->data['foo'] = new StdClass();
        $this->data['foo']->name = 'Foo';
        $this->data['foo']->number = 1;
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->data);
    }

    public function __get($name)
    {
        return $this->data[$name];
    }
}
