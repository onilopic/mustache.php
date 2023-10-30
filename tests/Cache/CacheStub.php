<?php

namespace Mustache\Tests\Cache;

use Mustache\Cache\AbstractCache;

class CacheStub extends AbstractCache
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
