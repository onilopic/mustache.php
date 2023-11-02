<?php

namespace Mustache\Tests\Functional;

use Mustache\Engine;
use PHPUnit\Framework\TestCase;

/**
 * @group sections
 * @group functional
 */
class ObjectSectionTest extends TestCase
{
    private Engine $mustache;

    public function setUp(): void
    {
        $this->mustache = new Engine();
    }

    public function testBasicObject()
    {
        $tpl = $this->mustache->loadTemplate('{{#foo}}{{name}}{{/foo}}');
        $this->assertEquals('Foo', $tpl->render(new Alpha()));
    }

    /**
     * @group magic_methods
     */
    public function testObjectWithGet()
    {
        $tpl = $this->mustache->loadTemplate('{{#foo}}{{name}}{{/foo}}');
        $this->assertEquals('Foo', $tpl->render(new Beta()));
    }

    /**
     * @group magic_methods
     */
    public function testSectionObjectWithGet()
    {
        $tpl = $this->mustache->loadTemplate('{{#bar}}{{#foo}}{{name}}{{/foo}}{{/bar}}');
        $this->assertEquals('Foo', $tpl->render(new Gamma()));
    }

    public function testSectionObjectWithFunction()
    {
        $tpl = $this->mustache->loadTemplate('{{#foo}}{{name}}{{/foo}}');
        $alpha = new Alpha();
        $alpha->foo = new Delta();
        $this->assertEquals('Foo', $tpl->render($alpha));
    }
}

class Alpha
{
    public $foo;

    public function __construct()
    {
        $this->foo = new \StdClass();
        $this->foo->name = 'Foo';
        $this->foo->number = 1;
    }
}

class Beta
{
    protected $_data = array();

    public function __construct()
    {
        $this->_data['foo'] = new \StdClass();
        $this->_data['foo']->name = 'Foo';
        $this->_data['foo']->number = 1;
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->_data);
    }

    public function __get($name)
    {
        return $this->_data[$name];
    }
}

class Gamma
{
    public $bar;

    public function __construct()
    {
        $this->bar = new Beta();
    }
}

class Delta
{
    protected $_name = 'Foo';

    public function name()
    {
        return $this->_name;
    }
}
