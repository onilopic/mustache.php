<?php

// phpcs:ignoreFile

namespace Mustache\Tests;

/**
 * Minimal stream wrapper to test protocol-based access to templates.
 */
final class TestStream
{
    private mixed $fileHandler;

    /**
     * Always returns false.
     *
     * @param string $path
     * @param int    $flags
     *
     * @return bool
     */
    public function url_stat(string $path, int $flags): bool
    {
        return false;
    }

    /**
     * Open the file.
     *
     * @param string $path
     * @param string $mode
     *
     * @return bool
     */
    public function stream_open(string $path, string $mode): bool
    {
        $path = preg_replace('-^test://-', '', $path);
        $this->fileHandler = fopen($path, $mode);

        return $this->fileHandler !== false;
    }

    /**
     * @return array
     */
    public function stream_stat(): array
    {
        return [];
    }

    /**
     * @param int $count
     *
     * @return string|false
     */
    public function stream_read(int $count): false|string
    {
        return fgets($this->fileHandler, $count);
    }

    /**
     * @return bool
     */
    public function stream_eof(): bool
    {
        return feof($this->fileHandler);
    }

    /**
     * @return bool
     */
    public function stream_close(): bool
    {
        return fclose($this->fileHandler);
    }
}
