<?php

namespace Mustache\Tests\FiveThree\Functional\Context;

use Closure;

final class Foo
{
    public string $name  = 'Justin';
    public string $lorem = 'Lorem ipsum dolor sit amet,';
    public Closure $wrap;
    public Closure $wrapper;

    public function __construct()
    {
        $this->wrap = fn($text): string => sprintf('<em>%s</em>', $text);
    }
}