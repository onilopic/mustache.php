<?php

namespace Mustache\Tests\Exception;

use UnexpectedValueException;

class UnknownFilterExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testInstance()
    {
        $e = new \Mustache\Exception\UnknownFilterException('bacon');
        $this->assertTrue($e instanceof UnexpectedValueException);
        $this->assertTrue($e instanceof \Mustache\Exception);
    }

    public function testMessage()
    {
        $e = new \Mustache\Exception\UnknownFilterException('sausage');
        $this->assertEquals('Unknown filter: sausage', $e->getMessage());
    }

    public function testGetFilterName()
    {
        $e = new \Mustache\Exception\UnknownFilterException('eggs');
        $this->assertEquals('eggs', $e->getFilterName());
    }

    public function testPrevious()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Exception chaining requires at least PHP 5.3');
        }

        $previous = new \Exception();
        $e = new \Mustache\Exception\UnknownFilterException('foo', $previous);

        $this->assertSame($previous, $e->getPrevious());
    }
}
