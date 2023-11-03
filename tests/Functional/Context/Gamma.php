<?php

namespace Mustache\Tests\Functional\Context;

final class Gamma
{
    public Beta $bar;

    public function __construct()
    {
        $this->bar = new Beta();
    }
}
