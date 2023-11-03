<?php

namespace Mustache\Tests\FiveThree\Functional;

use Mustache\Engine;
use PHPUnit\Framework\TestCase;
use StdClass;

/**
 * @group lambdas
 * @group functional
 */
class StrictCallablesTest extends TestCase
{
    /**
     * @dataProvider callables
     */
    public function testStrictCallables($strict, $name, $section, $expected)
    {
        $mustache = new Engine(['strict_callables' => $strict]);
        $tpl      = $mustache->loadTemplate('{{# section }}{{ name }}{{/ section }}');

        $data = new StdClass();
        $data->name    = $name;
        $data->section = $section;

        $this->assertEquals($expected, $tpl->render($data));
    }

    public static function callables(): array
    {
        $lambda = fn($tpl, $mustache): string => strtoupper($mustache->render($tpl));

        return [
            // Interpolation lambdas
            [
                false,
                [__CLASS__, 'instanceName'],
                $lambda,
                'YOSHI',
            ],
            [
                false,
                [__CLASS__, 'staticName'],
                $lambda,
                'YOSHI',
            ],
            [
                false,
                function () {
                    return 'Yoshi';
                },
                $lambda,
                'YOSHI',
            ],

            // Section lambdas
            [
                false,
                'Yoshi',
                [__CLASS__, 'instanceCallable'],
                'YOSHI',
            ],
            [
                false,
                'Yoshi',
                [__CLASS__, 'staticCallable'],
                'YOSHI',
            ],
            [
                false,
                'Yoshi',
                $lambda,
                'YOSHI',
            ],

            // Strict interpolation lambdas
            [
                true,
                function () {
                    return 'Yoshi';
                },
                $lambda,
                'YOSHI',
            ],

            // Strict section lambdas
            [
                true,
                'Yoshi',
                [__CLASS__, 'instanceCallable'],
                'YoshiYoshi',
            ],
            [
                true,
                'Yoshi',
                [__CLASS__, 'staticCallable'],
                'YoshiYoshi',
            ],
            [
                true,
                'Yoshi',
                function ($tpl, $mustache) {
                    return strtoupper($mustache->render($tpl));
                },
                'YOSHI',
            ],
        ];
    }

    public static function instanceCallable($tpl, $mustache): string
    {
        return strtoupper($mustache->render($tpl));
    }

    public static function staticCallable($tpl, $mustache): string
    {
        return strtoupper($mustache->render($tpl));
    }

    public static function instanceName(): string
    {
        return 'Yoshi';
    }

    public static function staticName(): string
    {
        return 'Yoshi';
    }
}
