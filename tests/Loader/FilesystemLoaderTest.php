<?php

namespace Mustache\Tests\Loader;

use Mustache\Exception\RuntimeException;
use Mustache\Exception\UnknownTemplateException;
use Mustache\Loader\FilesystemLoader;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class FilesystemLoaderTest extends TestCase
{
    public function testConstructor()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../fixtures/templates');
        $loader = new FilesystemLoader($baseDir, array('extension' => '.ms'));
        $this->assertEquals('alpha contents', $loader->load('alpha'));
        $this->assertEquals('beta contents', $loader->load('beta.ms'));
    }

    public function testTrailingSlashes()
    {
        $baseDir = dirname(__FILE__) . '/../fixtures/templates/';
        $loader = new FilesystemLoader($baseDir);
        $this->assertEquals('one contents', $loader->load('one'));
    }

    public function testConstructorWithProtocol()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../fixtures/templates');
        $loader = new FilesystemLoader('file://' . $baseDir, array('extension' => '.ms'));
        $this->assertEquals('alpha contents', $loader->load('alpha'));
        $this->assertEquals('beta contents', $loader->load('beta.ms'));
    }

    public function testLoadTemplates()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../fixtures/templates');
        $loader = new FilesystemLoader($baseDir);
        $this->assertEquals('one contents', $loader->load('one'));
        $this->assertEquals('two contents', $loader->load('two.mustache'));
    }

    public function testEmptyExtensionString()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../fixtures/templates');

        $loader = new FilesystemLoader($baseDir, array('extension' => ''));
        $this->assertEquals('one contents', $loader->load('one.mustache'));
        $this->assertEquals('alpha contents', $loader->load('alpha.ms'));

        $loader = new FilesystemLoader($baseDir, array('extension' => null));
        $this->assertEquals('two contents', $loader->load('two.mustache'));
        $this->assertEquals('beta contents', $loader->load('beta.ms'));
    }

    public function testMissingBaseDirThrowsException()
    {
        $this->expectException(RuntimeException::class);
        new FilesystemLoader(dirname(__FILE__) . '/not_a_directory');
    }

    public function testMissingTemplateThrowsException()
    {
        $this->expectException(UnknownTemplateException::class);
        $baseDir = realpath(dirname(__FILE__) . '/../fixtures/templates');
        $loader = new FilesystemLoader($baseDir);

        $loader->load('fake');
    }
}
