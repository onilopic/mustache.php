<?php

namespace Mustache\Tests\Functional;

use Mustache\Engine;
use PHPUnit\Framework\TestCase;

/**
 * @group functional
 * @group partials
 */
class NestedPartialIndentTest extends TestCase
{
    /**
     * @dataProvider partialsAndStuff
     */
    public function testNestedPartialsAreIndentedProperly($src, array $partials, $expected)
    {
        $m = new Engine([
            'partials' => $partials,
        ]);
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
