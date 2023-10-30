<?php


/**
 * @group unit
 */
class Mustache_Test_Loader_ArrayLoaderTest extends PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $loader = new Mustache_Loader_ArrayLoader(array(
            'foo' => 'bar',
        ));

        $this->assertEquals('bar', $loader->load('foo'));
    }

    public function testSetAndLoadTemplates()
    {
        $loader = new Mustache_Loader_ArrayLoader(array(
            'foo' => 'bar',
        ));
        $this->assertEquals('bar', $loader->load('foo'));

        $loader->setTemplate('baz', 'qux');
        $this->assertEquals('qux', $loader->load('baz'));

        $loader->setTemplates(array(
            'foo' => 'FOO',
            'baz' => 'BAZ',
        ));
        $this->assertEquals('FOO', $loader->load('foo'));
        $this->assertEquals('BAZ', $loader->load('baz'));
    }

    public function testMissingTemplatesThrowExceptions()
    {
        $this->expectException(Mustache_Exception_UnknownTemplateException::class);
        $loader = new Mustache_Loader_ArrayLoader();
        $loader->load('not_a_real_template');
    }
}
