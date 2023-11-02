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
        $foo = new Foo();
        $foo->doublewrap = array($foo, 'wrapWithBoth');

        $bar = new Foo();
        $bar->trimmer = array(get_class($bar), 'staticTrim');

        return array(
            array($foo, '{{#doublewrap}}{{name}}{{/doublewrap}}', sprintf('<strong><em>%s</em></strong>', $foo->name)),
            array($bar, '{{#trimmer}}   {{name}}   {{/trimmer}}', $bar->name),
        );
    }

    public function testViewArraySectionCallback()
    {
        $tpl = $this->mustache->loadTemplate('{{#trim}}    {{name}}    {{/trim}}');

        $foo = new Foo();

        $data = array(
            'name' => 'Bob',
            'trim' => array(get_class($foo), 'staticTrim'),
        );

        $this->assertEquals($data['name'], $tpl->render($data));
    }

    public function testMonsters()
    {
        $tpl = $this->mustache->loadTemplate('{{#title}}{{title}} {{/title}}{{name}}');

        $frank = new Monster();
        $frank->title = 'Dr.';
        $frank->name  = 'Frankenstein';
        $this->assertEquals('Dr. Frankenstein', $tpl->render($frank));

        $dracula = new Monster();
        $dracula->title = 'Count';
        $dracula->name  = 'Dracula';
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

        $foo = new Foo();
        $foo->wrap = array($foo, 'wrapWithEm');

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

        $foo = new Foo();
        $foo->wrap = array($foo, 'wrapWithEm');

        $this->assertEquals('<em>' . $foo->name . '</em>', $tpl->render($foo));
    }

    /**
     * @dataProvider cacheLambdaTemplatesData
     */
    public function testCacheLambdaTemplatesOptionWorks($dirName, $tplPrefix, $enable, $expect)
    {
        $cacheDir = $this->setUpCacheDir($dirName);
        $mustache = new Engine([
            'template_class_prefix'  => $tplPrefix,
            'cache'                  => $cacheDir,
            'cache_lambda_templates' => $enable,
        ]);

        $tpl = $mustache->loadTemplate('{{#wrap}}{{name}}{{/wrap}}');
        $foo = new Foo();
        $foo->wrap = array($foo, 'wrapWithEm');
        $this->assertEquals('<em>' . $foo->name . '</em>', $tpl->render($foo));
        $this->assertCount($expect, glob($cacheDir . '/*.php'));
    }

    public static function cacheLambdaTemplatesData(): array
    {
        return array(
            array('test_enabling_lambda_cache',  '_TestEnablingLambdaCache_',  true,  2),
            array('test_disabling_lambda_cache', '_TestDisablingLambdaCache_', false, 1),
        );
    }

    protected function setUpCacheDir($name): string
    {
        $cacheDir = self::$tempDir . '/' . $name;
        if (file_exists($cacheDir)) {
            self::rmdir($cacheDir);
        }
        mkdir($cacheDir, 0777, true);

        return $cacheDir;
    }
}

final class Foo
{
    public string $name = 'Justin';
    public string $lorem = 'Lorem ipsum dolor sit amet,';
    public array $wrap;
    public array $doublewrap;
    public array $trimmer;

    public function wrapWithEm($text): string
    {
        return sprintf('<em>%s</em>', $text);
    }

    /**
     * @param string $text
     * @return string
     */
    public function wrapWithStrong(string $text): string
    {
        return sprintf('<strong>%s</strong>', $text);
    }

    public function wrapWithBoth($text): string
    {
        return self::wrapWithStrong(self::wrapWithEm($text));
    }

    public static function staticTrim($text): string
    {
        return trim($text);
    }
}

final class Monster
{
    public string $title;
    public string $name;
}
