<?php

namespace Mustache\Tests\Logger;

class TestLogger extends \Mustache\Logger\AbstractLogger
{
    public $log = array();

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = array())
    {
        $this->log[] = array($level, $message, $context);
    }
}