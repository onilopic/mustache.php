<?php

namespace Mustache\Tests\Exception;

class UnknownHelperExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testInstance()
    {
        $e = new \Mustache\Exception\UnknownHelperException('alpha');
        $this->assertTrue($e instanceof \InvalidArgumentException);
        $this->assertTrue($e instanceof \Mustache\Exception);
    }

    public function testMessage()
    {
        $e = new \Mustache\Exception\UnknownHelperException('beta');
        $this->assertEquals('Unknown helper: beta', $e->getMessage());
    }

    public function testGetHelperName()
    {
        $e = new \Mustache\Exception\UnknownHelperException('gamma');
        $this->assertEquals('gamma', $e->getHelperName());
    }

    public function testPrevious()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Exception chaining requires at least PHP 5.3');
        }

        $previous = new \Exception();
        $e = new \Mustache\Exception\UnknownHelperException('foo', $previous);
        $this->assertSame($previous, $e->getPrevious());
    }
}
