<?php

/**
 * A PHPUnit test case wrapping the Mustache Spec.
 *
 * @group mustache-spec
 * @group functional
 */
class Mustache_Test_FiveThree_Functional_MustacheSpecTest extends Mustache_Test_SpecTestCase
{
    /**
     * For some reason data providers can't mark tests skipped, so this test exists
     * simply to provide a 'skipped' test if the `spec` submodule isn't initialized.
     */
    public function testSpecInitialized()
    {
        if (!file_exists(dirname(__FILE__) . '/../../../../../vendor/spec/specs/')) {
            $this->markTestSkipped('Mustache spec submodule not initialized: run "git submodule update --init"');
        } else {
            $this->assertEquals(true, file_exists(dirname(__FILE__) . '/../../../../../vendor/spec/specs/'));
        }
    }

    /**
     * @group lambdas
     * @dataProvider loadLambdasSpec
     */
    public function testLambdasSpec($desc, $source, $partials, $data, $expected)
    {
        $template = self::loadTemplate($source, $partials);
        $this->assertEquals($expected, $template($this->prepareLambdasSpec($data)), $desc);
    }

    public static function loadLambdasSpec()
    {
        return self::loadSpec('~lambdas');
    }

    /**
     * Extract and lambdafy any 'lambda' values found in the $data array.
     */
    private function prepareLambdasSpec($data)
    {
        foreach ($data as $key => $val) {
            if (isset($val['__tag__']) && $val['__tag__'] === 'code') {
                if (!isset($val['php'])) {
                    $this->markTestSkipped(sprintf('PHP lambda test not implemented for this test.'));
                    return;
                }

                $func = $val['php'];
                $data[$key] = function ($text = null) use ($func) {
                    return eval($func);
                };
            } elseif (is_array($val)) {
                $data[$key] = $this->prepareLambdasSpec($val);
            }
        }

        return $data;
    }
}
