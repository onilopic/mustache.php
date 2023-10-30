<?php

class Mustache_Test_Cache_AbstractCacheTest extends PHPUnit\Framework\TestCase
{
    public function testGetSetLogger()
    {
        $cache  = new CacheStub();
        $logger = new Mustache_Logger_StreamLogger('php://stdout');
        $cache->setLogger($logger);
        $this->assertSame($logger, $cache->getLogger());
    }

    public function testSetLoggerThrowsExceptions()
    {
        $this->expectException(Mustache_Exception_InvalidArgumentException::class);
        $cache  = new CacheStub();
        $logger = new StdClass();
        $cache->setLogger($logger);
    }
}

class CacheStub extends Mustache_Cache_AbstractCache
{
    public function load($key)
    {
        // nada
    }

    public function cache($key, $value)
    {
        // nada
    }
}
