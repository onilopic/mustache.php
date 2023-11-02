<?php

namespace Mustache\Tests\Cache;

use Mustache\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;


class AbstractCacheTest extends TestCase
{
    public function testGetSetLogger()
    {
        $cache  = new CacheStub();
        $logger = new \Mustache\Logger\StreamLogger('php://stdout');
        $cache->setLogger($logger);
        $this->assertSame($logger, $cache->getLogger());
    }

    public function testSetLoggerThrowsExceptions()
    {
        $this->expectException(InvalidArgumentException::class);
        $cache  = new CacheStub();
        $logger = new \StdClass();
        $cache->setLogger($logger);
    }
}


