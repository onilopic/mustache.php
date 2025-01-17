<?php

namespace Mustache\Tests;

use Mustache\Cache\FilesystemCache;
use Mustache\Cache\NoopCache;
use Mustache\Compiler;
use Mustache\Contract\Logger;
use Mustache\Engine;
use Mustache\Exception\InvalidArgumentException;
use Mustache\Exception\RuntimeException;
use Mustache\Loader\ArrayLoader;
use Mustache\Loader\ProductionFilesystemLoader;
use Mustache\Loader\StringLoader;
use Mustache\Logger\StreamLogger;
use Mustache\Parser;
use Mustache\Template;
use Mustache\Tokenizer;
use StdClass;
use TypeError;

/**
 * @group unit
 */
class EngineTest extends FunctionalTestCase
{
    public function testConstructor()
    {
        $logger = new StreamLogger(tmpfile());
        $loader = new StringLoader();
        $partialsLoader = new ArrayLoader();
        $mustache = new Engine(
            [
                'template_class_prefix' => '__whot__',
                'cache' => self::$tempDir,
                'cache_file_mode' => 777,
                'logger' => $logger,
                'loader' => $loader,
                'partials_loader' => $partialsLoader,
                'partials' => [
                    'foo' => '{{ foo }}',
                ],
                'helpers' => [
                    'foo' => [$this, 'getFoo'],
                    'bar' => 'BAR',
                ],
                'escape' => 'strtoupper',
                'entity_flags' => ENT_QUOTES,
                'charset' => 'ISO-8859-1',
                'pragmas' => [Engine::PRAGMA_FILTERS],
            ]
        );

        $this->assertSame($logger, $mustache->getLogger());
        $this->assertSame($loader, $mustache->getLoader());
        $this->assertSame($partialsLoader, $mustache->getPartialsLoader());
        $this->assertEquals('{{ foo }}', $partialsLoader->load('foo'));
        $this->assertStringContainsString('__whot__', $mustache->getTemplateClassName('{{ foo }}'));
        $this->assertEquals('strtoupper', $mustache->getEscape());
        $this->assertEquals(ENT_QUOTES, $mustache->getEntityFlags());
        $this->assertEquals('ISO-8859-1', $mustache->getCharset());
        $this->assertTrue($mustache->hasHelper('foo'));
        $this->assertTrue($mustache->hasHelper('bar'));
        $this->assertFalse($mustache->hasHelper('baz'));
        $this->assertInstanceOf('\Mustache\Cache\FilesystemCache', $mustache->getCache());
        $this->assertEquals([Engine::PRAGMA_FILTERS], $mustache->getPragmas());
    }

    public static function getFoo(): string
    {
        return 'foo';
    }

    public function testRender()
    {
        $source = '{{ foo }}';
        $data = ['bar' => 'baz'];
        $output = 'TEH OUTPUT';

        $template = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mustache = new MustacheStub();
        $mustache->template = $template;

        $template->expects($this->once())
            ->method('render')
            ->with($data)
            ->willReturn($output);

        $this->assertEquals($output, $mustache->render($source, $data));
        $this->assertEquals($source, $mustache->source);
    }

    public function testSettingServices()
    {
        $logger = new StreamLogger(tmpfile());
        $loader = new StringLoader();
        $tokenizer = new Tokenizer();
        $parser = new Parser();
        $compiler = new Compiler();
        $mustache = new Engine();
        $cache = new FilesystemCache(self::$tempDir);

        $this->assertNotSame($logger, $mustache->getLogger());
        $mustache->setLogger($logger);
        $this->assertSame($logger, $mustache->getLogger());

        $this->assertNotSame($loader, $mustache->getLoader());
        $mustache->setLoader($loader);
        $this->assertSame($loader, $mustache->getLoader());

        $this->assertNotSame($loader, $mustache->getPartialsLoader());
        $mustache->setPartialsLoader($loader);
        $this->assertSame($loader, $mustache->getPartialsLoader());

        $this->assertNotSame($tokenizer, $mustache->getTokenizer());
        $mustache->setTokenizer($tokenizer);
        $this->assertSame($tokenizer, $mustache->getTokenizer());

        $this->assertNotSame($parser, $mustache->getParser());
        $mustache->setParser($parser);
        $this->assertSame($parser, $mustache->getParser());

        $this->assertNotSame($compiler, $mustache->getCompiler());
        $mustache->setCompiler($compiler);
        $this->assertSame($compiler, $mustache->getCompiler());

        $this->assertNotSame($cache, $mustache->getCache());
        $mustache->setCache($cache);
        $this->assertSame($cache, $mustache->getCache());
    }

    /**
     * @group functional
     */
    public function testCache()
    {
        $mustache = new Engine(
            [
                'template_class_prefix' => '__whot__',
                'cache' => self::$tempDir,
            ]
        );

        $source = '{{ foo }}';
        $template = $mustache->loadTemplate($source);
        $className = $mustache->getTemplateClassName($source);

        $this->assertInstanceOf($className, $template);
    }

    public function testLambdaCache()
    {
        $mustache = new MustacheStub(
            [
                'cache' => self::$tempDir,
                'cache_lambda_templates' => true,
            ]
        );

        $this->assertNotInstanceOf(NoopCache::class, $mustache->getProtectedLambdaCache());
        $this->assertSame($mustache->getCache(), $mustache->getProtectedLambdaCache());
    }

    public function testWithoutLambdaCache()
    {
        $mustache = new MustacheStub(
            [
                'cache' => self::$tempDir,
            ]
        );

        $this->assertInstanceOf(NoopCache::class, $mustache->getProtectedLambdaCache());
        $this->assertNotSame($mustache->getCache(), $mustache->getProtectedLambdaCache());
    }

    public function testEmptyTemplatePrefixThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        new Engine(
            [
                'template_class_prefix' => '',
            ]
        );
    }

    /**
     *
     * @dataProvider getBadEscapers
     */
    public function testNonCallableEscapeThrowsException($escape)
    {
        $this->expectException(InvalidArgumentException::class);
        new Engine(['escape' => $escape]);
    }

    public static function getBadEscapers(): array
    {
        return [
            ['nothing'],
            ['foo', 'bar'],
        ];
    }

    public function testImmutablePartialsLoadersThrowException()
    {
        $this->expectException(RuntimeException::class);
        $mustache = new Engine(
            [
                'partials_loader' => new StringLoader(),
            ]
        );

        $mustache->setPartials(['foo' => '{{ foo }}']);
    }

    public function testMissingPartialsTreatedAsEmptyString()
    {
        $mustache = new Engine(
            [
                'partials_loader' => new ArrayLoader(
                    [
                        'foo' => 'FOO',
                        'baz' => 'BAZ',
                    ]
                ),
            ]
        );

        $this->assertEquals('FOOBAZ', $mustache->render('{{>foo}}{{>bar}}{{>baz}}', []));
    }

    public function testHelpers()
    {
        $foo = [$this, 'getFoo'];
        $bar = 'BAR';
        $mustache = new Engine(
            ['helpers' => [
                'foo' => $foo,
                'bar' => $bar,
            ]]
        );

        $helpers = $mustache->getHelpers();
        $this->assertTrue($mustache->hasHelper('foo'));
        $this->assertTrue($mustache->hasHelper('bar'));
        $this->assertTrue($helpers->has('foo'));
        $this->assertTrue($helpers->has('bar'));
        $this->assertSame($foo, $mustache->getHelper('foo'));
        $this->assertSame($bar, $mustache->getHelper('bar'));

        $mustache->removeHelper('bar');
        $this->assertFalse($mustache->hasHelper('bar'));
        $mustache->addHelper('bar', $bar);
        $this->assertSame($bar, $mustache->getHelper('bar'));

        $baz = [$this, 'wrapWithUnderscores'];
        $this->assertFalse($mustache->hasHelper('baz'));
        $this->assertFalse($helpers->has('baz'));

        $mustache->addHelper('baz', $baz);
        $this->assertTrue($mustache->hasHelper('baz'));
        $this->assertTrue($helpers->has('baz'));

        // ... and a functional test
        $tpl = $mustache->loadTemplate('{{foo}} - {{bar}} - {{#baz}}qux{{/baz}}');
        $this->assertEquals('foo - BAR - __qux__', $tpl->render());
        $this->assertEquals('foo - BAR - __qux__', $tpl->render(['qux' => "won't mess things up"]));
    }

    public static function wrapWithUnderscores($text): string
    {
        return '__' . $text . '__';
    }

    public function testSetHelpersThrowsExceptions()
    {
        $this->expectException(TypeError::class);
        $mustache = new Engine();
        /**
         * @noinspection PhpParamsInspection
         */
        $mustache->setHelpers('monkeymonkeymonkey');
    }

    public function testSetLoggerThrowsExceptions()
    {
        $this->expectException(TypeError::class);
        $mustache = new Engine();
        /** @noinspection PhpParamsInspection */
        $mustache->setLogger(new StdClass());
    }

    public function testLoadPartialCascading()
    {
        $loader = new ArrayLoader(
            [
                'foo' => 'FOO',
            ]
        );

        $mustache = new Engine(['loader' => $loader]);

        $tpl = $mustache->loadTemplate('foo');

        $this->assertSame($tpl, $mustache->loadPartial('foo'));

        $mustache->setPartials(
            [
                'foo' => 'f00',
            ]
        );

        // setting partials overrides the default template loading fallback.
        $this->assertNotSame($tpl, $mustache->loadPartial('foo'));

        // but it didn't overwrite the original template loader templates.
        $this->assertSame($tpl, $mustache->loadTemplate('foo'));
    }

    public function testPartialLoadFailLogging()
    {
        $name = tempnam(sys_get_temp_dir(), 'mustache-test');
        $mustache = new Engine(
            [
                'logger' => new StreamLogger($name, Logger::WARNING),
                'partials' => [
                    'foo' => 'FOO',
                    'bar' => 'BAR',
                ],
            ]
        );

        $result = $mustache->render('{{> foo }}{{> bar }}{{> baz }}', []);
        $this->assertEquals('FOOBAR', $result);

        $this->assertStringContainsString('WARNING: Partial not found: "baz"', file_get_contents($name));
    }

    public function testCacheWarningLogging()
    {
        [$name, $mustache] = $this->getLoggedMustache(Logger::WARNING);
        $mustache->render('{{ foo }}', ['foo' => 'FOO']);
        $this->assertStringContainsString('WARNING: Template cache disabled, evaluating', file_get_contents($name));
    }

    public function testLoggingIsNotTooAnnoying()
    {
        [$name, $mustache] = $this->getLoggedMustache();
        $mustache->render('{{ foo }}{{> bar }}', ['foo' => 'FOO']);
        $this->assertEmpty(file_get_contents($name));
    }

    public function testVerboseLoggingIsVerbose()
    {
        [$name, $mustache] = $this->getLoggedMustache(Logger::DEBUG);
        $mustache->render('{{ foo }}{{> bar }}', ['foo' => 'FOO']);
        $log = file_get_contents($name);
        $this->assertStringContainsString('DEBUG: Instantiating template: ', $log);
        $this->assertStringContainsString('WARNING: Partial not found: "bar"', $log);
    }

    public function testUnknownPragmaThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        new Engine(
            [
                'pragmas' => ['UNKNOWN'],
            ]
        );
    }

    public function testCompileFromMustacheSourceInstance()
    {
        $baseDir = realpath(dirname(__FILE__) . '/fixtures/templates');
        $mustache = new Engine(
            [
                'loader' => new ProductionFilesystemLoader($baseDir),
            ]
        );
        $this->assertEquals('one contents', $mustache->render('one'));
    }

    private function getLoggedMustache($level = Logger::ERROR): array
    {
        $name = tempnam(sys_get_temp_dir(), 'mustache-test');
        $mustache = new Engine(
            [
                'logger' => new StreamLogger($name, $level),
            ]
        );

        return [$name, $mustache];
    }

    public function testCustomDelimiters()
    {
        $mustache = new Engine(
            [
                'delimiters' => '[[ ]]',
                'partials' => [
                    'one' => '[[> two ]]',
                    'two' => '[[ a ]]',
                ],
            ]
        );

        $tpl = $mustache->loadTemplate('[[# a ]][[ b ]][[/a ]]');
        $this->assertEquals('c', $tpl->render(['a' => true, 'b' => 'c']));

        $tpl = $mustache->loadTemplate('[[> one ]]');
        $this->assertEquals('b', $tpl->render(['a' => 'b']));
    }
}
