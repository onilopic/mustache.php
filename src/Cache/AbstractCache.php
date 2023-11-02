<?php declare(strict_types=1);

namespace Mustache\Cache;

use Mustache\Cache;
use Mustache\Exception\InvalidArgumentException;

/**
 * Abstract Mustache Cache class.
 *
 * Provides logging support to child implementations.
 *
 * @abstract
 */
abstract class AbstractCache implements Cache
{
    private mixed $logger = null;

    /**
     * Get the current logger instance.
     *
     * @return \Mustache\Logger|Psr\Log\LoggerInterface
     */
    public function getLogger(): mixed
    {
        return $this->logger;
    }

    /**
     * Set a logger instance.
     *
     * @param \Mustache\Logger|Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger = null)
    {
        if ($logger !== null && !($logger instanceof \Mustache\Logger || is_a($logger, 'Psr\\Log\\LoggerInterface'))) {
            throw new InvalidArgumentException('Expected an instance of \Mustache\Logger or Psr\\Log\\LoggerInterface.');
        }

        $this->logger = $logger;
    }

    /**
     * Add a log record if logging is enabled.
     *
     * @param string $level   The logging level
     * @param string $message The log message
     * @param array  $context The log context
     */
    protected function log(string $level, string $message, array $context = [])
    {
        if (isset($this->logger)) {
            $this->logger->log($level, $message, $context);
        }
    }
}
