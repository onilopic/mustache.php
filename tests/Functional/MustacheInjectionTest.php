<?php

namespace Mustache\Tests\Functional;

use Mustache\Engine;
use PHPUnit\Framework\TestCase;

/**
 * @group mustache_injection
 * @group functional
 */
class MustacheInjectionTest extends TestCase
{
    private Engine $mustache;

    public function setUp(): void
    {
        $this->mustache = new Engine();
    }

    /**
     * @dataProvider injectionData
     */
    public function testInjection($tpl, $data, $partials, $expect)
    {
        $this->mustache->setPartials($partials);
        $this->assertEquals($expect, $this->mustache->render($tpl, $data));
    }

    public static function injectionData(): array
    {
        $interpolationData = [
            'a' => '{{ b }}',
            'b' => 'FAIL',
        ];

        $sectionData = [
            'a' => true,
            'b' => '{{ c }}',
            'c' => 'FAIL',
        ];

        $lambdaInterpolationData = [
            'a' => [__CLASS__, 'lambdaInterpolationCallback'],
            'b' => '{{ c }}',
            'c' => 'FAIL',
        ];

        $lambdaSectionData = [
            'a' => [__CLASS__, 'lambdaSectionCallback'],
            'b' => '{{ c }}',
            'c' => 'FAIL',
        ];

        return [
            ['{{ a }}',   $interpolationData, [], '{{ b }}'],
            ['{{{ a }}}', $interpolationData, [], '{{ b }}'],

            ['{{# a }}{{ b }}{{/ a }}',   $sectionData, [], '{{ c }}'],
            ['{{# a }}{{{ b }}}{{/ a }}', $sectionData, [], '{{ c }}'],

            ['{{> partial }}', $interpolationData, ['partial' => '{{ a }}'],   '{{ b }}'],
            ['{{> partial }}', $interpolationData, ['partial' => '{{{ a }}}'], '{{ b }}'],

            ['{{ a }}',           $lambdaInterpolationData, [], '{{ c }}'],
            ['{{# a }}b{{/ a }}', $lambdaSectionData,       [], '{{ c }}'],
        ];
    }

    public static function lambdaInterpolationCallback(): string
    {
        return '{{ b }}';
    }

    public static function lambdaSectionCallback($text): string
    {
        return '{{ ' . $text . ' }}';
    }
}
