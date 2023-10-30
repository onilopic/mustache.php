<?php

/**
 * @group unit
 */
class Mustache_Test_Loader_StringLoaderTest extends PHPUnit\Framework\TestCase
{
    public function testLoadTemplates()
    {
        $loader = new Mustache_Loader_StringLoader();

        $this->assertEquals('foo', $loader->load('foo'));
        $this->assertEquals('{{ bar }}', $loader->load('{{ bar }}'));
        $this->assertEquals("\n{{! comment }}\n", $loader->load("\n{{! comment }}\n"));
    }
}
