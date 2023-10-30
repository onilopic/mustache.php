<?php

/**
 * @group magic_methods
 * @group functional
 */
class Mustache_Test_Functional_CallTest extends PHPUnit\Framework\TestCase
{
    public function testCallEatsContext()
    {
        $m = new Mustache_Engine();
        $tpl = $m->loadTemplate('{{# foo }}{{ label }}: {{ name }}{{/ foo }}');

        $foo = new Mustache_Test_Functional_ClassWithCall();
        $foo->name = 'Bob';

        $data = array('label' => 'name', 'foo' => $foo);

        $this->assertEquals('name: Bob', $tpl->render($data));
    }
}

class Mustache_Test_Functional_ClassWithCall
{
    public $name;

    public function __call($method, $args)
    {
        return 'unknown value';
    }
}
