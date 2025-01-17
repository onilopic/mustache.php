<?php

namespace Mustache\Tests\Loader;

use Mustache\Exception\InvalidArgumentException;
use Mustache\Exception\UnknownTemplateException;
use Mustache\Loader\InlineLoader;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class InlineLoaderTest extends TestCase
{
    public function testLoadTemplates()
    {
        $loader = new InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__);
        $this->assertEquals('{{ foo }}', $loader->load('foo'));
        $this->assertEquals('{{#bar}}BAR{{/bar}}', $loader->load('bar'));
    }

    public function testMissingTemplatesThrowExceptions()
    {
        $this->expectException(UnknownTemplateException::class);
        $loader = new InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__);
        $loader->load('not_a_real_template');
    }

    public function testInvalidOffsetThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        new InlineLoader(__FILE__, -1);
    }

    public function testInvalidFileThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        new InlineLoader('notarealfile', __COMPILER_HALT_OFFSET__);
    }
}
//phpcs:disable
__halt_compiler();

@@ foo
{{ foo }}

@@ bar
{{#bar}}BAR{{/bar}}