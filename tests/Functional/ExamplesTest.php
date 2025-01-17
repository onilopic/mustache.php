<?php

namespace Mustache\Tests\Functional;

/**
 * @group examples
 * @group functional
 */
class ExamplesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test everything in the `examples` directory.
     *
     * @dataProvider getExamples
     *
     * @param string $context
     * @param string $source
     * @param array  $partials
     * @param string $expected
     */
    public function testExamples($context, $source, $partials, $expected)
    {
        $mustache = new \Mustache\Engine(
            [
            'partials' => $partials,
            ]
        );
        $this->assertEquals($expected, $mustache->loadTemplate($source)->render($context));
    }

    /**
     * Data provider for testExamples method.
     *
     * Loads examples from the test fixtures directory.
     *
     * This examples directory should contain any number of subdirectories, each of which contains
     * three files: one Mustache class (.php), one Mustache template (.mustache), and one output file
     * (.txt). Optionally, the directory may contain a folder full of partials.
     *
     * @return array
     */
    public static function getExamples()
    {
        $path     = realpath(dirname(__FILE__) . '/../fixtures/examples');
        $examples = [];

        $handle   = opendir($path);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullpath = $path . '/' . $file;
            if (is_dir($fullpath)) {
                $examples[$file] = self::loadExample($fullpath);
            }
        }
        closedir($handle);

        return $examples;
    }

    /**
     * Helper method to load an example given the full path.
     *
     * @param string $path
     *
     * @return array arguments for testExamples
     */
    private static function loadExample($path): array
    {
        $context  = null;
        $source   = null;
        $partials = [];
        $expected = null;

        $handle = opendir($path);
        while (($file = readdir($handle)) !== false) {
            $fullpath = $path . '/' . $file;
            $info = pathinfo($fullpath);

            if (is_dir($fullpath) && $info['basename'] === 'partials') {
                // load partials
                $partials = self::loadPartials($fullpath);
            } elseif (is_file($fullpath)) {
                // load other files
                switch ($info['extension']) {
                    case 'php':
                        include_once $fullpath;
                        $className = $info['filename'];
                        $context   = new $className();
                        break;

                    case 'mustache':
                        $source   = file_get_contents($fullpath);
                        break;

                    case 'txt':
                        $expected = file_get_contents($fullpath);
                        break;
                }
            }
        }
        closedir($handle);

        return [$context, $source, $partials, $expected];
    }

    /**
     * Helper method to load partials given an example directory.
     *
     * @param string $path
     *
     * @return array $partials
     */
    private static function loadPartials($path): array
    {
        $partials = [];

        $handle = opendir($path);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullpath = $path . '/' . $file;
            $info = pathinfo($fullpath);

            if ($info['extension'] === 'mustache') {
                $partials[$info['filename']] = file_get_contents($fullpath);
            }
        }
        closedir($handle);

        return $partials;
    }
}
