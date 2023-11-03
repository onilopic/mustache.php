<?php

namespace Mustache\Tests\FiveThree\Functional;

use DateTime;
use DateTimeZone;
use Exception;
use Mustache\Engine;
use PHPUnit\Framework\TestCase;

/**
 * @group pragmas
 * @group functional
 */
class EngineTest extends TestCase
{
    /**
     * @dataProvider pragmaData
     */
    public function testPragmasConstructorOption($pragmas, $helpers, $data, $tpl, $expect)
    {
        $mustache = new Engine(
            [
            'pragmas' => $pragmas,
            'helpers' => $helpers,
            ]
        );

        $this->assertEquals($expect, $mustache->render($tpl, $data));
    }

    /**
     * @throws Exception
     */
    public static function pragmaData(): array
    {
        $helpers = [
            'longdate' => function (DateTime $value) {
                return $value->format('Y-m-d h:m:s');
            },
        ];

        $data = [
            'date' => new DateTime('1/1/2000', new DateTimeZone('UTC')),
        ];

        $tpl = '{{ date | longdate }}';

        return [
            [[Engine::PRAGMA_FILTERS], $helpers, $data, $tpl, '2000-01-01 12:01:00'],
            [[], $helpers, $data, $tpl, ''],
        ];
    }
}
