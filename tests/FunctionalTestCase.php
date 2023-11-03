<?php

namespace Mustache\Tests;

use PHPUnit\Framework\TestCase;

abstract class FunctionalTestCase extends TestCase
{
    protected static string $tempDir;

    public static function setUpBeforeClass(): void
    {
        self::$tempDir = sys_get_temp_dir() . '/mustache_test';
        if (file_exists(self::$tempDir)) {
            self::rmdir(self::$tempDir);
        }
    }

    /**
     * @param string $path
     */
    protected static function rmdir(string $path)
    {
        $path = rtrim($path, '/') . '/';
        $handle = opendir($path);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = $path . $file;
            if (is_dir($fullPath)) {
                self::rmdir($fullPath);
            } else {
                unlink($fullPath);
            }
        }

        closedir($handle);
        rmdir($path);
    }
}
