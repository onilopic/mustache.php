<?php

namespace Mustache\Tests\Functional;

/**
 * A PHPUnit test case wrapping the Mustache Spec.
 *
 * @group mustache-spec
 * @group functional
 */
class MustacheDynamicNamesSpecTest extends \Mustache\Tests\SpecTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::$mustache = new \Mustache\Engine(
            [
            'pragmas' => [\Mustache\Engine::PRAGMA_DYNAMIC_NAMES],
            ]
        );
    }

    /**
     * For some reason data providers can't mark tests skipped, so this test exists
     * simply to provide a 'skipped' test if the `spec` submodule isn't initialized.
     */
    public function testSpecInitialized()
    {
        if (!file_exists(dirname(__FILE__) . '/../../spec/specs/')) {
            $this->markTestSkipped('Mustache spec submodule not initialized: run "git submodule update --init"');
        } else {
            $this->assertEquals(true, file_exists(dirname(__FILE__) . '/../../spec/specs/'));
        }
    }

    /**
     * @group        dynamic-names
     * @dataProvider loadDynamicNamesSpec
     */
    public function testDynamicNamesSpec($desc, $source, $partials, $data, $expected)
    {
        $template = self::loadTemplate($source, $partials);
        $this->assertEquals($expected, $template->render($data), $desc);
    }

    public static function loadDynamicNamesSpec(): array
    {
        return self::loadSpec('~dynamic-names');
    }
}
