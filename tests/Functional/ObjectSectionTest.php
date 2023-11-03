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
        $this->assertEquals('Foo', $tpl->render(new Context\Alpha()));
    }

    /**
     * @group magic_methods
     */
    public function testObjectWithGet()
    {
        $tpl = $this->mustache->loadTemplate('{{#foo}}{{name}}{{/foo}}');
        $this->assertEquals('Foo', $tpl->render(new Context\Beta()));
    }

    /**
     * @group magic_methods
     */
    public function testSectionObjectWithGet()
    {
        $tpl = $this->mustache->loadTemplate('{{#bar}}{{#foo}}{{name}}{{/foo}}{{/bar}}');
        $this->assertEquals('Foo', $tpl->render(new Context\Gamma()));
    }

    public function testSectionObjectWithFunction()
    {
        $tpl = $this->mustache->loadTemplate('{{#foo}}{{name}}{{/foo}}');
        $alpha = new Context\Alpha();
        $alpha->foo = new Context\Delta();
        $this->assertEquals('Foo', $tpl->render($alpha));
    }
}
