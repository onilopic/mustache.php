<?php

namespace Mustache\Tests\Functional;

use Mustache\Engine;
use Mustache\Tests\FunctionalTestCase;

/**
 * @group lambdas
 * @group functional
 */
class HigherOrderSectionsTest extends FunctionalTestCase
{
    private Engine $mustache;

    public function setUp(): void
    {
        $this->mustache = new Engine();
    }

    /**
     * @dataProvider sectionCallbackData
     */
    public function testSectionCallback($data, $tpl, $expect)
    {
        $this->assertEquals($expect, $this->mustache->render($tpl, $data));
    }

    public static function sectionCallbackData(): array
    {
        $foo = new Context\Foo();
        $foo->doublewrap = [$foo, 'wrapWithBoth'];

        $bar = new Context\Foo();
        $bar->trimmer = [get_class($bar), 'staticTrim'];

        return [
            [$foo, '{{#doublewrap}}{{name}}{{/doublewrap}}', sprintf('<strong><em>%s</em></strong>', $foo->name)],
            [$bar, '{{#trimmer}}   {{name}}   {{/trimmer}}', $bar->name],
        ];
    }

    public function testViewArraySectionCallback()
    {
        $tpl = $this->mustache->loadTemplate('{{#trim}}    {{name}}    {{/trim}}');

        $foo = new Context\Foo();

        $data = [
            'name' => 'Bob',
            'trim' => [get_class($foo), 'staticTrim'],
        ];

        $this->assertEquals($data['name'], $tpl->render($data));
    }

    public function testMonsters()
    {
        $tpl = $this->mustache->loadTemplate('{{#title}}{{title}} {{/title}}{{name}}');

        $frank = new Context\Monster();
        $frank->title = 'Dr.';
        $frank->name = 'Frankenstein';
        $this->assertEquals('Dr. Frankenstein', $tpl->render($frank));

        $dracula = new Context\Monster();
        $dracula->title = 'Count';
        $dracula->name = 'Dracula';
        $this->assertEquals('Count Dracula', $tpl->render($dracula));
    }

    public function testPassthroughOptimization()
    {
        $mustache = $this->getMockBuilder(Engine::class)
            ->onlyMethods(['loadLambda'])
            ->getMock();
        $mustache->expects($this->never())
            ->method('loadLambda');

        $tpl = $mustache->loadTemplate('{{#wrap}}NAME{{/wrap}}');

        $foo = new Context\Foo();
        $foo->wrap = [$foo, 'wrapWithEm'];

        $this->assertEquals('<em>NAME</em>', $tpl->render($foo));
    }

    public function testWithoutPassthroughOptimization()
    {
        $mustache = $this->getMockBuilder(Engine::class)
            ->onlyMethods(['loadLambda'])
            ->getMock();
        $mustache->expects($this->once())
            ->method('loadLambda')
            ->willReturn($mustache->loadTemplate('<em>{{ name }}</em>'));

        $tpl = $mustache->loadTemplate('{{#wrap}}{{name}}{{/wrap}}');

        $foo = new Context\Foo();
        $foo->wrap = [$foo, 'wrapWithEm'];

        $this->assertEquals('<em>' . $foo->name . '</em>', $tpl->render($foo));
    }

    /**
     * @dataProvider cacheLambdaTemplatesData
     */
    public function testCacheLambdaTemplatesOptionWorks($dirName, $tplPrefix, $enable, $expect)
    {
        $cacheDir = $this->setUpCacheDir($dirName);
        $mustache = new Engine(
            [
                'template_class_prefix' => $tplPrefix,
                'cache' => $cacheDir,
                'cache_lambda_templates' => $enable,
            ]
        );

        $tpl = $mustache->loadTemplate('{{#wrap}}{{name}}{{/wrap}}');
        $foo = new Context\Foo();
        $foo->wrap = [$foo, 'wrapWithEm'];
        $this->assertEquals('<em>' . $foo->name . '</em>', $tpl->render($foo));
        $this->assertCount($expect, glob($cacheDir . '/*.php'));
    }

    public static function cacheLambdaTemplatesData(): array
    {
        return [
            ['test_enabling_lambda_cache', '_TestEnablingLambdaCache_', true, 2],
            ['test_disabling_lambda_cache', '_TestDisablingLambdaCache_', false, 1],
        ];
    }

    protected function setUpCacheDir($name): string
    {
        $cacheDir = self::$tempDir . '/' . $name;
        if (file_exists($cacheDir)) {
            self::rmdir($cacheDir);
        }
        mkdir($cacheDir, 0o777, true);

        return $cacheDir;
    }
}
