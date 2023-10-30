<?php

/**
 * @group pragmas
 * @group functional
 */
class Mustache_Test_FiveThree_Functional_EngineTest extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider pragmaData
     */
    public function testPragmasConstructorOption($pragmas, $helpers, $data, $tpl, $expect)
    {
        $mustache = new Mustache_Engine(array(
            'pragmas' => $pragmas,
            'helpers' => $helpers,
        ));

        $this->assertEquals($expect, $mustache->render($tpl, $data));
    }

    public static function pragmaData()
    {
        $helpers = [
            'longdate' => function (\DateTime $value) {
                return $value->format('Y-m-d h:m:s');
            },
        ];

        $data = [
            'date' => new DateTime('1/1/2000', new DateTimeZone('UTC')),
        ];

        $tpl = '{{ date | longdate }}';

        return [
            [[Mustache_Engine::PRAGMA_FILTERS], $helpers, $data, $tpl, '2000-01-01 12:01:00'],
            [[], $helpers, $data, $tpl, ''],
        ];
    }
}
