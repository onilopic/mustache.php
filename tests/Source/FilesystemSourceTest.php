<?php

namespace Mustache\Tests\Source;

/**
 * @group unit
 */
class FilesystemSourceTest extends \PHPUnit\Framework\TestCase
{
    public function testMissingTemplateThrowsException()
    {
        $this->expectException(\Mustache\Exception\RuntimeException::class);
        $source = new \Mustache\Source\FilesystemSource(dirname(__FILE__) . '/not_a_file.mustache', array('mtime'));
        $source->getKey();
    }
}
