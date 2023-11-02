<?php

namespace Mustache\Tests;

abstract class SpecTestCase extends \PHPUnit\Framework\TestCase
{
    protected static $mustache;

    public static function setUpBeforeClass(): void
    {
        self::$mustache = new \Mustache\Engine();
    }

    protected static function loadTemplate($source, $partials)
    {
        self::$mustache->setPartials($partials);

        return self::$mustache->loadTemplate($source);
    }

    /**
     * Data provider for the mustache spec test.
     *
     * Loads JSON files from the spec and converts them to PHPisms.
     *
     * @param string $name
     *
     * @return array
     */
    protected static function loadSpec($name)
    {
        $filename = dirname(__FILE__) . '/../vendor/spec/specs/' . $name . '.json';
        if (!file_exists($filename)) {
            return array();
        }

        $data = array();
        $file = file_get_contents($filename);
        $spec = json_decode($file, true);

        foreach ($spec['tests'] as $test) {
            $data[] = array(
                $test['name'] . ': ' . $test['desc'],
                $test['template'],
                isset($test['partials']) ? $test['partials'] : array(),
                $test['data'],
                $test['expected'],
            );
        }

        return $data;
    }
}
