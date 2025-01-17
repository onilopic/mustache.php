<?php

namespace Mustache\Tests\Loader;

use Mustache\Loader\StringLoader;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class StringLoaderTest extends TestCase
{
    public function testLoadTemplates()
    {
        $loader = new StringLoader();

        $this->assertEquals('foo', $loader->load('foo'));
        $this->assertEquals('{{ bar }}', $loader->load('{{ bar }}'));
        $this->assertEquals("\n{{! comment }}\n", $loader->load("\n{{! comment }}\n"));
    }
}
