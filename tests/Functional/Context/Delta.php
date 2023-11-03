<?php

namespace Mustache\Tests\Functional\Context;

final class Delta
{
    protected string $value = 'Foo';

    public function name(): string
    {
        return $this->value;
    }
}
