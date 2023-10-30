<?php

/**
 * A PHPUnit test case wrapping the Mustache Spec.
 *
 * @group mustache-spec
 * @group functional
 */
class Mustache_Test_Functional_MustacheInheritanceSpecTest extends Mustache_Test_SpecTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::$mustache = new Mustache_Engine(array(
          'pragmas' => array(Mustache_Engine::PRAGMA_BLOCKS),
        ));
    }

    /**
     * For some reason data providers can't mark tests skipped, so this test exists
     * simply to provide a 'skipped' test if the `spec` submodule isn't initialized.
     */
    public function testSpecInitialized()
    {
        $fileExist = file_exists(dirname(__FILE__) . '/../../../../vendor/spec/specs/');
        if (!$fileExist) {
            $this->markTestSkipped('Mustache spec submodule not initialized: run "git submodule update --init"');
        } else {
            $this->assertEquals(true, $fileExist);
        }
    }

    /**
     * @group inheritance
     * @dataProvider loadInheritanceSpec
     */
    public function testInheritanceSpec($desc, $source, $partials, $data, $expected)
    {
        $template = self::loadTemplate($source, $partials);
        $this->assertEquals($expected, $template->render($data), $desc);
    }

    public static function loadInheritanceSpec()
    {
        // return $this->loadSpec('sections');
        // return [];
        // die;
        return self::loadSpec('~inheritance');
    }
}
