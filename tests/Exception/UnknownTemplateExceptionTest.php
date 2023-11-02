<?php

namespace Mustache\Tests\Exception;

use Exception;
use InvalidArgumentException;
use Mustache\Exception\UnknownTemplateException;
use PHPUnit\Framework\TestCase;

class UnknownTemplateExceptionTest extends TestCase
{
    public function testInstance()
    {
        $e = new UnknownTemplateException('mario');
        $resultInvalidArgumentException = $e instanceof InvalidArgumentException;
        $resultMustacheException = $e instanceof \Mustache\Exception;
        $this->assertTrue($resultInvalidArgumentException);
        $this->assertTrue($resultMustacheException);
    }

    public function testMessage()
    {
        $e = new UnknownTemplateException('luigi');
        $this->assertEquals('Unknown template: luigi', $e->getMessage());
    }

    public function testGetTemplateName()
    {
        $e = new UnknownTemplateException('yoshi');
        $this->assertEquals('yoshi', $e->getTemplateName());
    }

    public function testPrevious()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Exception chaining requires at least PHP 5.3');
        }

        $previous = new Exception();
        $e = new UnknownTemplateException('foo', $previous);
        $this->assertSame($previous, $e->getPrevious());
    }
}
