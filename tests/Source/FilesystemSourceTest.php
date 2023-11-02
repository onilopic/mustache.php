<?php

namespace Mustache\Tests\Source;

use Mustache\Exception\RuntimeException;
use Mustache\Source\FilesystemSource;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class FilesystemSourceTest extends TestCase
{
    public function testMissingTemplateThrowsException()
    {
        $this->expectException(RuntimeException::class);
        $source = new FilesystemSource(dirname(__FILE__) . '/not_a_file.mustache', array('mtime'));
        $source->getKey();
    }
}
