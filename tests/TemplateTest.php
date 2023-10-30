<?php

namespace Mustache\Tests;

use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class TemplateTest extends TestCase
{
    public function testConstructor()
    {
        $mustache = new \Mustache\Engine();
        $template = new TemplateStub($mustache);
        $this->assertSame($mustache, $template->getMustache());
    }

    public function testRendering()
    {
        $rendered = '<< wheee >>';
        $mustache = new \Mustache\Engine();
        $template = new TemplateStub($mustache);
        $template->rendered = $rendered;
        $context  = new \Mustache\Context();

        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            $this->assertEquals($rendered, $template());
        }

        $this->assertEquals($rendered, $template->render());
        $this->assertEquals($rendered, $template->renderInternal($context));
        $this->assertEquals($rendered, $template->render(array('foo' => 'bar')));
    }
}
