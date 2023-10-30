<?php

/**
 * @group mustache_injection
 * @group functional
 */
class Mustache_Test_Functional_MustacheInjectionTest extends PHPUnit\Framework\TestCase
{
    private $mustache;

    public function setUp(): void
    {
        $this->mustache = new \Mustache\Engine();
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

        $lambdaSectionData = array(
            'a' => array(__CLASS__, 'lambdaSectionCallback'),
            'b' => '{{ c }}',
            'c' => 'FAIL',
        );

        return array(
            array('{{ a }}',   $interpolationData, array(), '{{ b }}'),
            array('{{{ a }}}', $interpolationData, array(), '{{ b }}'),

            array('{{# a }}{{ b }}{{/ a }}',   $sectionData, array(), '{{ c }}'),
            array('{{# a }}{{{ b }}}{{/ a }}', $sectionData, array(), '{{ c }}'),

            array('{{> partial }}', $interpolationData, array('partial' => '{{ a }}'),   '{{ b }}'),
            array('{{> partial }}', $interpolationData, array('partial' => '{{{ a }}}'), '{{ b }}'),

            array('{{ a }}',           $lambdaInterpolationData, array(), '{{ c }}'),
            array('{{# a }}b{{/ a }}', $lambdaSectionData,       array(), '{{ c }}'),
        );
    }

    public static function lambdaInterpolationCallback()
    {
        return '{{ b }}';
    }

    public static function lambdaSectionCallback($text)
    {
        return '{{ ' . $text . ' }}';
    }
}
