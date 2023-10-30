<?php

/**
 * @group functional
 * @group partials
 */
class Mustache_Test_Functional_NestedPartialIndentTest extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider partialsAndStuff
     */
    public function testNestedPartialsAreIndentedProperly($src, array $partials, $expected)
    {
        $m = new \Mustache\Engine(array(
            'partials' => $partials,
        ));
        $tpl = $m->loadTemplate($src);
        $this->assertEquals($expected, $tpl->render());
    }

    public static function partialsAndStuff(): array
    {
        $partials = [
            'a' => ' {{> b }}',
            'b' => ' {{> d }}',
            'c' => ' {{> d }}{{> d }}',
            'd' => 'D!',
        ];

        return [
            [' {{> a }}', $partials, '   D!'],
            [' {{> b }}', $partials, '  D!'],
            [' {{> c }}', $partials, '  D!D!'],
        ];
    }
}
