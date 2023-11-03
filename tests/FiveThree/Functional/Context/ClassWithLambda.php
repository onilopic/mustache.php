<?php

namespace Mustache\Tests\FiveThree\Functional\Context;

use Closure;

final class ClassWithLambda
{
    public function toUpper(): Closure
    {
        return fn($val) => strtoupper($val);
    }

    public function placeholder(): Closure
    {
        return fn() => 'Enter your name';
    }
}
