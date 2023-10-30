<?php

namespace Mustache\Tests\Exception;

use LogicException;
use Mustache\Exception\SyntaxException;
use Mustache\Tokenizer;

class SyntaxExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testInstance()
    {
        $e = new SyntaxException('whot', array('is' => 'this'));
        $this->assertTrue($e instanceof LogicException);
        $this->assertTrue($e instanceof \Mustache\Exception);
    }

    public function testGetToken()
    {
        $token = array(Tokenizer::TYPE => 'whatever');
        $e = new SyntaxException('ignore this', $token);
        $this->assertEquals($token, $e->getToken());
    }

    public function testPrevious()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Exception chaining requires at least PHP 5.3');
        }

        $previous = new \Exception();
        $e = new SyntaxException('foo', array(), $previous);

        $this->assertSame($previous, $e->getPrevious());
    }
}
