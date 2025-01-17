<?php

namespace Mustache\Tests\FiveThree\Functional;

use Mustache\Tests\SpecTestCase;

/**
 * A PHPUnit test case wrapping the Mustache Spec.
 *
 * @group mustache-spec
 * @group functional
 */
class MustacheSpecTest extends SpecTestCase
{
    /**
     * For some reason data providers can't mark tests skipped, so this test exists
     * simply to provide a 'skipped' test if the `spec` submodule isn't initialized.
     */
    public function testSpecInitialized()
    {
        if (!file_exists(dirname(__FILE__) . '/../../../spec/specs/')) {
            $this->markTestSkipped('Mustache spec submodule not initialized: run "git submodule update --init"');
        } else {
            $this->assertEquals(true, file_exists(dirname(__FILE__) . '/../../../spec/specs/'));
        }
    }

    /**
     * @group        lambdas
     * @dataProvider loadLambdasSpec
     */
    public function testLambdasSpec($desc, $source, $partials, $data, $expected)
    {
        $template = self::loadTemplate($source, $partials);
        $this->assertEquals($expected, $template($this->prepareLambdasSpec($data)), $desc);
    }

    public static function loadLambdasSpec(): array
    {
        return self::loadSpec('~lambdas');
    }

    /**
     * Extract and lambda by any 'lambda' values found in the $data array.
     */
    private function prepareLambdasSpec($data)
    {
        foreach ($data as $key => $val) {
            if (isset($val['__tag__']) && $val['__tag__'] === 'code') {
                if (!isset($val['php'])) {
                    $this->markTestSkipped('PHP lambda test not implemented for this test.');
                }

                $func = $val['php'];
                $data[$key] = fn($text = null) => eval($func);
            } elseif (is_array($val)) {
                $data[$key] = $this->prepareLambdasSpec($val);
            }
        }

        return $data;
    }
}
