<?php

namespace Mustache\Tests\Loader;


use Mustache\Exception\UnknownTemplateException;
use Mustache\Loader\ArrayLoader;
use Mustache\Loader\CascadingLoader;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class CascadingLoaderTest extends TestCase
{
    public function testLoadTemplates()
    {
        $loader = new CascadingLoader([
            new ArrayLoader(['foo' => '{{ foo }}']),
            new ArrayLoader(['bar' => '{{#bar}}BAR{{/bar}}']),
        ]);

        $this->assertEquals('{{ foo }}', $loader->load('foo'));
        $this->assertEquals('{{#bar}}BAR{{/bar}}', $loader->load('bar'));
    }

    public function testMissingTemplatesThrowExceptions()
    {
        $this->expectException(UnknownTemplateException::class);
        $loader = new CascadingLoader([
            new ArrayLoader(['foo' => '{{ foo }}']),
            new ArrayLoader(['bar' => '{{#bar}}BAR{{/bar}}']),
        ]);

        $loader->load('not_a_real_template');
    }
}
