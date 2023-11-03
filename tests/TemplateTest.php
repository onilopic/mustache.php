<?php

namespace Mustache\Tests;

use Mustache\Context;
use Mustache\Engine;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class TemplateTest extends TestCase
{
    public function testConstructor()
    {
        $mustache = new Engine();
        $template = new TemplateStub($mustache);
        $this->assertSame($mustache, $template->getMustache());
    }

    public function testRendering()
    {
        $rendered = '<< wheee >>';
        $mustache = new Engine();
        $template = new TemplateStub($mustache);
        $template->rendered = $rendered;
        $context  = new Context();

        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            $this->assertEquals($rendered, $template());
        }

        $this->assertEquals($rendered, $template->render());
        $this->assertEquals($rendered, $template->renderInternal($context));
        $this->assertEquals($rendered, $template->render(['foo' => 'bar']));
    }
}
