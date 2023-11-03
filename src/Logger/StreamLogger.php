<?php

namespace Mustache\Logger;

use Mustache\Contract\Logger;
use Mustache\Exception\InvalidArgumentException;
use Mustache\Exception\LogicException;
use Mustache\Exception\RuntimeException;

/**
 * A Mustache Stream Logger.
 *
 * The Stream Logger wraps a file resource instance (such as a stream) or a
 * stream URL. All log messages over the threshold level will be appended to
 * this stream.
 *
 * Hint: Try `php://stderr` for your stream URL.
 */
class StreamLogger extends AbstractLogger
{
    protected static array $levels = [
        self::DEBUG     => 100,
        self::INFO      => 200,
        self::NOTICE    => 250,
        self::WARNING   => 300,
        self::ERROR     => 400,
        self::CRITICAL  => 500,
        self::ALERT     => 550,
        self::EMERGENCY => 600,
    ];

    protected int|string $level;
    protected mixed $stream = null;
    protected mixed $url    = null;

    /**
     * @param resource|string $stream Resource instance or URL
     * @param string|int $level The minimum logging level at which this handler will be triggered
     */
    public function __construct(mixed $stream, string|int $level = Logger::ERROR)
    {
        $this->setLevel($level);

        if (is_resource($stream)) {
            $this->stream = $stream;
        } else {
            $this->url = $stream;
        }
    }

    /**
     * Close stream resources.
     */
    public function __destruct()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    /**
     * Set the minimum logging level.
     *
     * @param string|int $level The minimum logging level which will be written
     */
    public function setLevel(string|int $level)
    {
        if (!array_key_exists($level, self::$levels)) {
            throw new InvalidArgumentException(sprintf('Unexpected logging level: %s', $level));
        }

        $this->level = $level;
    }

    /**
     * Get the current minimum logging level.
     *
     * @return int|string
     */
    public function getLevel(): int|string
    {
        return $this->level;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *@throws InvalidArgumentException if the logging level is unknown
     *
     */
    public function log(mixed $level, string $message, array $context = [])
    {
        if (!array_key_exists($level, self::$levels)) {
            throw new InvalidArgumentException(sprintf('Unexpected logging level: %s', $level));
        }

        if (self::$levels[$level] >= self::$levels[$this->level]) {
            $this->writeLog($level, $message, $context);
        }
    }

    /**
     * Write a record to the log.
     *
     * @param int|string $level The logging level
     * @param string $message The log message
     * @param array $context The log context
     */
    protected function writeLog(int|string $level, string $message, array $context = [])
    {
        if (!is_resource($this->stream)) {
            if (!isset($this->url)) {
                throw new LogicException(
                    'Missing stream url, the stream can not be opened.'
                    . ' This may be caused by a premature call to close().'
                );
            }

            $this->stream = fopen($this->url, 'a');
            if (!is_resource($this->stream)) {
                // @codeCoverageIgnoreStart
                throw new RuntimeException(sprintf('The stream or file "%s" could not be opened.', $this->url));
                // @codeCoverageIgnoreEnd
            }
        }

        fwrite($this->stream, self::formatLine($level, $message, $context));
    }

    /**
     * Gets the name of the logging level.
     *
     * @param string|int $level
     *
     * @return string
     */
    protected static function getLevelName(string|int $level): string
    {
        return strtoupper((string)$level);
    }

    /**
     * Format a log line for output.
     *
     * @param string|int $level The logging level
     * @param string $message The log message
     * @param array $context The log context
     *
     * @return string
     */
    protected static function formatLine(string|int $level, string $message, array $context = []): string
    {
        return sprintf(
            "%s: %s\n",
            self::getLevelName($level),
            self::interpolateMessage($message, $context)
        );
    }

    /**
     * Interpolate context values into the message placeholders.
     *
     * @param string $message
     * @param array  $context
     *
     * @return string
     */
    protected static function interpolateMessage(string $message, array $context = []): string
    {
        if (!str_contains($message, '{')) {
            return $message;
        }

        // build a replacement array with braces around the context keys
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}
