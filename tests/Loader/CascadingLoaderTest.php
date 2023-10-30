<?php


/**
 * @group unit
 */
class Mustache_Test_Loader_CascadingLoaderTest extends PHPUnit\Framework\TestCase
{
    public function testLoadTemplates()
    {
        $loader = new \Mustache\Loader\CascadingLoader(array(
            new \Mustache\Loader\ArrayLoader(array('foo' => '{{ foo }}')),
            new \Mustache\Loader\ArrayLoader(array('bar' => '{{#bar}}BAR{{/bar}}')),
        ));

        $this->assertEquals('{{ foo }}', $loader->load('foo'));
        $this->assertEquals('{{#bar}}BAR{{/bar}}', $loader->load('bar'));
    }

    public function testMissingTemplatesThrowExceptions()
    {
        $this->expectException(\Mustache\Exception\UnknownTemplateException::class);
        $loader = new \Mustache\Loader\CascadingLoader(array(
            new \Mustache\Loader\ArrayLoader(array('foo' => '{{ foo }}')),
            new \Mustache\Loader\ArrayLoader(array('bar' => '{{#bar}}BAR{{/bar}}')),
        ));

        $loader->load('not_a_real_template');
    }
}
