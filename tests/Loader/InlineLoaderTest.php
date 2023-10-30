<?php

/**
 * @group unit
 */
class Mustache_Test_Loader_InlineLoaderTest extends PHPUnit\Framework\TestCase
{
    public function testLoadTemplates()
    {
        $loader = new \Mustache\Loader\InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__);
        $this->assertEquals('{{ foo }}', $loader->load('foo'));
        $this->assertEquals('{{#bar}}BAR{{/bar}}', $loader->load('bar'));
    }

    public function testMissingTemplatesThrowExceptions()
    {
        $this->expectException(\Mustache\Exception\UnknownTemplateException::class);
        $loader = new \Mustache\Loader\InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__);
        $loader->load('not_a_real_template');
    }

    public function testInvalidOffsetThrowsException()
    {
        $this->expectException(\Mustache\Exception\InvalidArgumentException::class);
        new \Mustache\Loader\InlineLoader(__FILE__, 'notanumber');
    }

    public function testInvalidFileThrowsException()
    {
        $this->expectException(\Mustache\Exception\InvalidArgumentException::class);
        new \Mustache\Loader\InlineLoader('notarealfile', __COMPILER_HALT_OFFSET__);
    }
}

__halt_compiler();

@@ foo
{{ foo }}

@@ bar
{{#bar}}BAR{{/bar}}
