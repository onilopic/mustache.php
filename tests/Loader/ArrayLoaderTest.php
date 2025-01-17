<?php

namespace Mustache\Tests\Loader;

use Mustache\Exception\UnknownTemplateException;
use Mustache\Loader\ArrayLoader;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class ArrayLoaderTest extends TestCase
{
    public function testConstructor()
    {
        $loader = new ArrayLoader(
            [
            'foo' => 'bar',
            ]
        );

        $this->assertEquals('bar', $loader->load('foo'));
    }

    public function testSetAndLoadTemplates()
    {
        $loader = new ArrayLoader(
            [
            'foo' => 'bar',
            ]
        );
        $this->assertEquals('bar', $loader->load('foo'));

        $loader->setTemplate('baz', 'qux');
        $this->assertEquals('qux', $loader->load('baz'));

        $loader->setTemplates(
            [
            'foo' => 'FOO',
            'baz' => 'BAZ',
            ]
        );
        $this->assertEquals('FOO', $loader->load('foo'));
        $this->assertEquals('BAZ', $loader->load('baz'));
    }

    public function testMissingTemplatesThrowExceptions()
    {
        $this->expectException(UnknownTemplateException::class);
        $loader = new ArrayLoader();
        $loader->load('not_a_real_template');
    }
}
