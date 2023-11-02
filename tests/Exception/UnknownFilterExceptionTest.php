<?php

namespace Mustache\Tests\Exception;

use Exception;
use Mustache\Exception\UnknownFilterException;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class UnknownFilterExceptionTest extends TestCase
{
    public function testInstance()
    {
        $e = new UnknownFilterException('bacon');
        $resultUnexpectedValueException = $e instanceof UnexpectedValueException;
        $resultMustacheException = $e instanceof \Mustache\Exception;
        $this->assertTrue($resultUnexpectedValueException);
        $this->assertTrue($resultMustacheException);
    }

    public function testMessage()
    {
        $e = new UnknownFilterException('sausage');
        $this->assertEquals('Unknown filter: sausage', $e->getMessage());
    }

    public function testGetFilterName()
    {
        $e = new UnknownFilterException('eggs');
        $this->assertEquals('eggs', $e->getFilterName());
    }

    public function testPrevious()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Exception chaining requires at least PHP 5.3');
        }

        $previous = new Exception();
        $e = new UnknownFilterException('foo', $previous);

        $this->assertSame($previous, $e->getPrevious());
    }
}
