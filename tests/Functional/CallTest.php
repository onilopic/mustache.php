<?php

namespace Mustache\Tests\Functional;

use Mustache\Engine;
use PHPUnit\Framework\TestCase;

/**
 * @group magic_methods
 * @group functional
 */
class CallTest extends TestCase
{
    public function testCallEatsContext()
    {
        $m = new Engine();
        $tpl = $m->loadTemplate('{{# foo }}{{ label }}: {{ name }}{{/ foo }}');

        $foo = new class () {
            public string $name;

            public function __call($method, $args)
            {
                return 'unknown value';
            }
        };

        $foo->name = 'Bob';

        $data = ['label' => 'name', 'foo' => $foo];

        $this->assertEquals('name: Bob', $tpl->render($data));
    }
}
