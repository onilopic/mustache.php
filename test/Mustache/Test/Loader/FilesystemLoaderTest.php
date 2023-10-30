<?php

/**
 * @group unit
 */
class Mustache_Test_Loader_FilesystemLoaderTest extends PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');
        $loader = new Mustache_Loader_FilesystemLoader($baseDir, array('extension' => '.ms'));
        $this->assertEquals('alpha contents', $loader->load('alpha'));
        $this->assertEquals('beta contents', $loader->load('beta.ms'));
    }

    public function testTrailingSlashes()
    {
        $baseDir = dirname(__FILE__) . '/../../../fixtures/templates/';
        $loader = new Mustache_Loader_FilesystemLoader($baseDir);
        $this->assertEquals('one contents', $loader->load('one'));
    }

    public function testConstructorWithProtocol()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');

        $loader = new Mustache_Loader_FilesystemLoader('test://' . $baseDir, array('extension' => '.ms'));
        $this->assertEquals('alpha contents', $loader->load('alpha'));
        $this->assertEquals('beta contents', $loader->load('beta.ms'));
    }

    public function testLoadTemplates()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');
        $loader = new Mustache_Loader_FilesystemLoader($baseDir);
        $this->assertEquals('one contents', $loader->load('one'));
        $this->assertEquals('two contents', $loader->load('two.mustache'));
    }

    public function testEmptyExtensionString()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');

        $loader = new Mustache_Loader_FilesystemLoader($baseDir, array('extension' => ''));
        $this->assertEquals('one contents', $loader->load('one.mustache'));
        $this->assertEquals('alpha contents', $loader->load('alpha.ms'));

        $loader = new Mustache_Loader_FilesystemLoader($baseDir, array('extension' => null));
        $this->assertEquals('two contents', $loader->load('two.mustache'));
        $this->assertEquals('beta contents', $loader->load('beta.ms'));
    }

    public function testMissingBaseDirThrowsException()
    {
        $this->expectException(Mustache_Exception_RuntimeException::class);
        new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/not_a_directory');
    }

    public function testMissingTemplateThrowsException()
    {
        $this->expectException(Mustache_Exception_UnknownTemplateException::class);
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');
        $loader = new Mustache_Loader_FilesystemLoader($baseDir);

        $loader->load('fake');
    }
}
