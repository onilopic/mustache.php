<?php

namespace Mustache\Tests\Logger;

use Mustache\Logger\AbstractLogger;

class TestLogger extends AbstractLogger
{
    public array $log = [];

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log(mixed $level, string $message, array $context = [])
    {
        $this->log[] = [$level, $message, $context];
    }
}
