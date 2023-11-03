<?php

namespace Mustache\Tests\FiveThree\Functional;

use DateTime;
use DateTimeZone;
use Exception;
use Mustache\Engine;
use Mustache\Exception\UnknownFilterException;
use PHPUnit\Framework\TestCase;
use StdClass;

/**
 * @group filters
 * @group functional
 */
class FiltersTest extends TestCase
{
    private Engine $mustache;

    public function setUp(): void
    {
        $this->mustache = new Engine();
    }

    /**
     * @dataProvider singleFilterData
     */
    public function testSingleFilter($tpl, $helpers, $data, $expect)
    {
        $this->mustache->setHelpers($helpers);
        $this->assertEquals($expect, $this->mustache->render($tpl, $data));
    }

    /**
     * @throws Exception
     */
    public static function singleFilterData(): array
    {
        $helpers = [
            'longdate' => function (DateTime $value) {
                return $value->format('Y-m-d h:m:s');
            },
            'echo' => function ($value) {
                return [$value, $value, $value];
            },
        ];

        return [
            [
                '{{% FILTERS }}{{ date | longdate }}',
                $helpers,
                (object)['date' => new DateTime('1/1/2000', new DateTimeZone('UTC'))],
                '2000-01-01 12:01:00',
            ],

            [
                '{{% FILTERS }}{{# word | echo }}{{ . }}!{{/ word | echo }}',
                $helpers,
                ['word' => 'bacon'],
                'bacon!bacon!bacon!',
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function testChainedFilters()
    {
        $tpl = $this->mustache->loadTemplate('{{% FILTERS }}{{ date | longdate | withbrackets }}');

        $this->mustache->addHelper(
            'longdate',
            function (DateTime $value) {
                return $value->format('Y-m-d h:m:s');
            }
        );

        $this->mustache->addHelper(
            'withbrackets',
            function ($value) {
                return sprintf('[[%s]]', $value);
            }
        );

        $foo = new StdClass();
        $foo->date = new DateTime('1/1/2000', new DateTimeZone('UTC'));

        $this->assertEquals('[[2000-01-01 12:01:00]]', $tpl->render($foo));
    }

    private const CHAINED_SECTION_FILTERS_TPL = <<<'EOS'
        {{% FILTERS }}
        {{# word | echo | with_index }}
        {{ key }}: {{ value }}
        {{/ word | echo | with_index }}
        EOS;

    public function testChainedSectionFilters()
    {
        $tpl = $this->mustache->loadTemplate(self::CHAINED_SECTION_FILTERS_TPL);

        $this->mustache->addHelper(
            'echo',
            function ($value) {
                return [$value, $value, $value];
            }
        );

        $this->mustache->addHelper(
            'with_index',
            function ($value) {
                return array_map(
                    fn($k, $v) => [
                    'key' => $k,
                    'value' => $v,
                    ],
                    array_keys($value),
                    $value
                );
            }
        );

        $this->assertEquals("0: bacon\n1: bacon\n2: bacon\n", $tpl->render(['word' => 'bacon']));
    }

    /**
     * @dataProvider interpolateFirstData
     */
    public function testInterpolateFirst($tpl, $data, $expect)
    {
        $this->assertEquals($expect, $this->mustache->render($tpl, $data));
    }

    public static function interpolateFirstData(): array
    {
        $data = [
            'foo' => 'FOO',
            'bar' => fn($value): string => ($value === 'FOO') ? 'win!' : 'fail :(',
        ];

        return [
            ['{{% FILTERS }}{{ foo | bar }}', $data, 'win!'],
            ['{{% FILTERS }}{{# foo | bar }}{{ . }}{{/ foo | bar }}', $data, 'win!'],
        ];
    }

    /**
     *
     * @dataProvider brokenPipeData
     */
    public function testThrowsExceptionForBrokenPipes($tpl, $data)
    {
        $this->expectException(UnknownFilterException::class);
        $this->mustache->render($tpl, $data);
    }

    public static function brokenPipeData(): array
    {
        return [
            ['{{% FILTERS }}{{ foo | bar }}', []],
            ['{{% FILTERS }}{{ foo | bar }}', ['foo' => 'FOO']],
            ['{{% FILTERS }}{{ foo | bar }}', ['foo' => 'FOO', 'bar' => 'BAR']],
            ['{{% FILTERS }}{{ foo | bar }}', ['foo' => 'FOO', 'bar' => [1, 2]]],
            ['{{% FILTERS }}{{ foo | bar | baz }}', ['foo' => 'FOO', 'bar' => fn() => 'BAR']],
            ['{{% FILTERS }}{{ foo | bar | baz }}', ['foo' => 'FOO', 'baz' => fn() => 'BAZ']],
            ['{{% FILTERS }}{{ foo | bar | baz }}', ['bar' => fn() => 'BAR']],
            ['{{% FILTERS }}{{ foo | bar | baz }}', ['baz' => fn() => 'BAZ']],
            ['{{% FILTERS }}{{ foo | bar.baz }}', ['foo' => 'FOO', 'bar' => fn() => 'BAR', 'baz' => fn() => 'BAZ']],

            ['{{% FILTERS }}{{# foo | bar }}{{ . }}{{/ foo | bar }}', []],
            ['{{% FILTERS }}{{# foo | bar }}{{ . }}{{/ foo | bar }}', ['foo' => 'FOO']],
            ['{{% FILTERS }}{{# foo | bar }}{{ . }}{{/ foo | bar }}', ['foo' => 'FOO', 'bar' => 'BAR']],
            ['{{% FILTERS }}{{# foo | bar }}{{ . }}{{/ foo | bar }}', ['foo' => 'FOO', 'bar' => [1, 2]]],
            [
                '{{% FILTERS }}{{# foo | bar | baz }}{{ . }}{{/ foo | bar | baz }}',
                ['foo' => 'FOO', 'bar' => fn() => 'BAR'],
            ],
            [
                '{{% FILTERS }}{{# foo | bar | baz }}{{ . }}{{/ foo | bar | baz }}',
                ['foo' => 'FOO', 'baz' => fn() => 'BAZ'],
            ],
            ['{{% FILTERS }}{{# foo | bar | baz }}{{ . }}{{/ foo | bar | baz }}', ['bar' => fn() => 'BAR']],
            ['{{% FILTERS }}{{# foo | bar | baz }}{{ . }}{{/ foo | bar | baz }}', ['baz' => fn() => 'BAZ']],
            [
                '{{% FILTERS }}{{# foo | bar.baz }}{{ . }}{{/ foo | bar.baz }}',
                ['foo' => 'FOO', 'bar' => fn() => 'BAR', 'baz' => fn() => 'BAZ'],
            ],
        ];
    }
}
