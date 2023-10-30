<?php

/**
 * @group unit
 */
class Mustache_Test_Source_FilesystemSourceTest extends PHPUnit\Framework\TestCase
{
    public function testMissingTemplateThrowsException()
    {
        $this->expectException(Mustache_Exception_RuntimeException::class);
        $source = new Mustache_Source_FilesystemSource(dirname(__FILE__) . '/not_a_file.mustache', array('mtime'));
        $source->getKey();
    }
}
