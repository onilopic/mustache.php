<?php

namespace Mustache\Tests\Exception;

use Exception;
use InvalidArgumentException;
use Mustache\Exception\UnknownHelperException;
use PHPUnit\Framework\TestCase;

class UnknownHelperExceptionTest extends TestCase
{
    public function testInstance()
    {
        $e = new UnknownHelperException('alpha');
        $resultInvalidArgumentException = $e instanceof InvalidArgumentException;
        $resultMustacheException = $e instanceof \Mustache\Contract\Exception;
        $this->assertTrue($resultInvalidArgumentException);
        $this->assertTrue($resultMustacheException);
    }

    public function testMessage()
    {
        $e = new UnknownHelperException('beta');
        $this->assertEquals('Unknown helper: beta', $e->getMessage());
    }

    public function testGetHelperName()
    {
        $e = new UnknownHelperException('gamma');
        $this->assertEquals('gamma', $e->getHelperName());
    }

    public function testPrevious()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Exception chaining requires at least PHP 5.3');
        }

        $previous = new Exception();
        $e = new UnknownHelperException('foo', $previous);
        $this->assertSame($previous, $e->getPrevious());
    }
}
