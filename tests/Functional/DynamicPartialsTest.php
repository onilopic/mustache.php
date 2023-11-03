<?php

namespace Mustache\Tests\Functional;

use Mustache\Engine;
use Mustache\Exception\SyntaxException;
use PHPUnit\Framework\TestCase;

/**
 * @group dynamic-names
 * @group functional
 */
class DynamicPartialsTest extends TestCase
{
    private Engine $mustache;

    public function setUp(): void
    {
        $this->mustache = new Engine(
            [
            'pragmas' => [Engine::PRAGMA_DYNAMIC_NAMES],
            ]
        );
    }

    public static function getValidDynamicNamesExamples(): array
    {
        // technically not all dynamic names, but also not invalid
        return [
            ['{{>* foo }}'],
            ['{{>* foo.bar.baz }}'],
            ['{{=* *=}}'],
            ['{{! *foo }}'],
            ['{{! foo.*bar }}'],
            ['{{% FILTERS }}{{! foo | *bar }}'],
            ['{{% BLOCKS }}{{< *foo }}{{/ *foo }}'],
        ];
    }

    /**
     * @dataProvider getValidDynamicNamesExamples
     */
    public function testLegalInheritanceExamples($template)
    {
        $this->assertSame('', $this->mustache->render($template));
    }

    public static function getDynamicNameParseErrors(): array
    {
        return [
            ['{{# foo }}{{/ *foo }}'],
            ['{{^ foo }}{{/ *foo }}'],
            ['{{% BLOCKS }}{{< foo }}{{/ *foo }}'],
            ['{{% BLOCKS }}{{$ foo }}{{/ *foo }}'],
        ];
    }

    /**
     * @dataProvider getDynamicNameParseErrors
     */
    public function testDynamicNameParseErrors($template)
    {
        $this->expectExceptionMessage("Nesting error:");
        $this->expectException(SyntaxException::class);
        $this->mustache->render($template);
    }


    public function testDynamicBlocks()
    {
        $tpl = '{{% BLOCKS }}{{< *partial }}{{$ bar }}{{ value }}{{/ bar }}{{/ *partial }}';

        $this->mustache->setPartials(
            array(
            'foobarbaz' => '{{% BLOCKS }}{{$ foo }}foo{{/ foo }}{{$ bar }}bar{{/ bar }}{{$ baz }}baz{{/ baz }}',
            'qux' => 'qux',
            )
        );

        $result = $this->mustache->render(
            $tpl,
            array(
            'partial' => 'foobarbaz',
            'value' => 'BAR',
            )
        );

        $this->assertSame('fooBARbaz', $result);
    }
}
