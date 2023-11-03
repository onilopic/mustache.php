<?php

namespace Mustache\Tests\Exception;

use Exception;
use LogicException;
use Mustache\Exception\SyntaxException;
use Mustache\Tokenizer;
use PHPUnit\Framework\TestCase;

class SyntaxExceptionTest extends TestCase
{
    public function testInstance()
    {
        $e = new SyntaxException('whot', ['is' => 'this']);
        $resultLogicException = $e instanceof LogicException;
        $resultMustacheException = $e instanceof \Mustache\Contract\Exception;
        $this->assertTrue($resultLogicException);
        $this->assertTrue($resultMustacheException);
    }

    public function testGetToken()
    {
        $token = [Tokenizer::TYPE => 'whatever'];
        $e = new SyntaxException('ignore this', $token);
        $this->assertEquals($token, $e->getToken());
    }

    public function testPrevious()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Exception chaining requires at least PHP 5.3');
        }

        $previous = new Exception();
        $e = new SyntaxException('foo', [], $previous);

        $this->assertSame($previous, $e->getPrevious());
    }
}
