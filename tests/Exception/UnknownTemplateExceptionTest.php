<?php

namespace Mustache\Tests\Exception;

use Mustache\Exception\UnknownTemplateException;

class UnknownTemplateExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testInstance()
    {
        $e = new UnknownTemplateException('mario');
        $this->assertTrue($e instanceof \InvalidArgumentException);
        $this->assertTrue($e instanceof \Mustache\Exception);
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

        $previous = new \Exception();
        $e = new UnknownTemplateException('foo', $previous);
        $this->assertSame($previous, $e->getPrevious());
    }
}
