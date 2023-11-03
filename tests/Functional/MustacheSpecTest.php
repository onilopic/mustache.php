<?php

namespace Mustache\Tests\Functional;

/**
 * A PHPUnit test case wrapping the Mustache Spec.
 *
 * @group mustache-spec
 * @group functional
 */
class MustacheSpecTest extends \Mustache\Tests\SpecTestCase
{
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
     * @group        comments
     * @dataProvider loadCommentSpec
     */
    public function testCommentSpec($desc, $source, $partials, $data, $expected)
    {
        $template = self::loadTemplate($source, $partials);
        $this->assertEquals($expected, $template->render($data), $desc);
    }

    public static function loadCommentSpec(): array
    {
        return self::loadSpec('comments');
    }

    /**
     * @group        delimiters
     * @dataProvider loadDelimitersSpec
     */
    public function testDelimitersSpec($desc, $source, $partials, $data, $expected)
    {
        $template = self::loadTemplate($source, $partials);
        $this->assertEquals($expected, $template->render($data), $desc);
    }

    public static function loadDelimitersSpec(): array
    {
        return self::loadSpec('delimiters');
    }

    /**
     * @group        interpolation
     * @dataProvider loadInterpolationSpec
     */
    public function testInterpolationSpec($desc, $source, $partials, $data, $expected)
    {
        $template = self::loadTemplate($source, $partials);
        $this->assertEquals($expected, $template->render($data), $desc);
    }

    public static function loadInterpolationSpec()
    {
        return self::loadSpec('interpolation');
    }

    /**
     * @group        inverted
     * @group        inverted-sections
     * @dataProvider loadInvertedSpec
     */
    public function testInvertedSpec($desc, $source, $partials, $data, $expected)
    {
        $template = self::loadTemplate($source, $partials);
        $this->assertEquals($expected, $template->render($data), $desc);
    }

    public static function loadInvertedSpec()
    {
        return self::loadSpec('inverted');
    }

    /**
     * @group        partials
     * @dataProvider loadPartialsSpec
     */
    public function testPartialsSpec($desc, $source, $partials, $data, $expected)
    {
        $template = self::loadTemplate($source, $partials);
        $this->assertEquals($expected, $template->render($data), $desc);
    }

    public static function loadPartialsSpec()
    {
        return self::loadSpec('partials');
    }

    /**
     * @group        sections
     * @dataProvider loadSectionsSpec
     */
    public function testSectionsSpec($desc, $source, $partials, $data, $expected)
    {
        $template = self::loadTemplate($source, $partials);
        $this->assertEquals($expected, $template->render($data), $desc);
    }

    public static function loadSectionsSpec()
    {
        return self::loadSpec('sections');
    }
}
