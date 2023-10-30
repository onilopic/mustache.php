<?php

namespace Mustache\Tests;

use Mustache\Tokenizer;

/**
 * @group unit
 */
class EngineTest extends FunctionalTestCase
{
    public function testConstructor()
    {
        $logger         = new \Mustache\Logger\StreamLogger(tmpfile());
        $loader         = new \Mustache\Loader\StringLoader();
        $partialsLoader = new \Mustache\Loader\ArrayLoader();
        $mustache       = new \Mustache\Engine([
            'template_class_prefix' => '__whot__',
            'cache'                 => self::$tempDir,
            'cache_file_mode'       => 777,
            'logger'                => $logger,
            'loader'                => $loader,
            'partials_loader'       => $partialsLoader,
            'partials'              => [
                'foo' => '{{ foo }}',
            ],
            'helpers' => [
                'foo' => [$this, 'getFoo'],
                'bar' => 'BAR',
            ],
            'escape'       => 'strtoupper',
            'entity_flags' => ENT_QUOTES,
            'charset'      => 'ISO-8859-1',
            'pragmas'      => [\Mustache\Engine::PRAGMA_FILTERS],
        ]);

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
        $this->assertEquals(array(\Mustache\Engine::PRAGMA_FILTERS), $mustache->getPragmas());
    }

    public static function getFoo()
    {
        return 'foo';
    }

    public function testRender()
    {
        $source = '{{ foo }}';
        $data   = array('bar' => 'baz');
        $output = 'TEH OUTPUT';

        $template = $this->getMockBuilder(\Mustache\Template::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mustache = new MustacheStub();
        $mustache->template = $template;

        $template->expects($this->once())
            ->method('render')
            ->with($data)
            ->will($this->returnValue($output));

        $this->assertEquals($output, $mustache->render($source, $data));
        $this->assertEquals($source, $mustache->source);
    }

    public function testSettingServices()
    {
        $logger    = new \Mustache\Logger\StreamLogger(tmpfile());
        $loader    = new \Mustache\Loader\StringLoader();
        $tokenizer = new Tokenizer();
        $parser    = new \Mustache\Parser();
        $compiler  = new \Mustache\Compiler();
        $mustache  = new \Mustache\Engine();
        $cache     = new \Mustache\Cache\FilesystemCache(self::$tempDir);

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
        $mustache = new \Mustache\Engine(array(
            'template_class_prefix' => '__whot__',
            'cache'                 => self::$tempDir,
        ));

        $source    = '{{ foo }}';
        $template  = $mustache->loadTemplate($source);
        $className = $mustache->getTemplateClassName($source);

        $this->assertInstanceOf($className, $template);
    }

    public function testLambdaCache()
    {
        $mustache = new MustacheStub(array(
            'cache'                  => self::$tempDir,
            'cache_lambda_templates' => true,
        ));

        $this->assertNotInstanceOf('Mustache_Cache_NoopCache', $mustache->getProtectedLambdaCache());
        $this->assertSame($mustache->getCache(), $mustache->getProtectedLambdaCache());
    }

    public function testWithoutLambdaCache()
    {
        $mustache = new MustacheStub(array(
            'cache' => self::$tempDir,
        ));

        $this->assertInstanceOf('Mustache_Cache_NoopCache', $mustache->getProtectedLambdaCache());
        $this->assertNotSame($mustache->getCache(), $mustache->getProtectedLambdaCache());
    }

    public function testEmptyTemplatePrefixThrowsException()
    {
        $this->expectException(\Mustache\Exception\InvalidArgumentException::class);
        new \Mustache\Engine(array(
            'template_class_prefix' => '',
        ));
    }

    /**
     *
     * @dataProvider getBadEscapers
     */
    public function testNonCallableEscapeThrowsException($escape)
    {
        $this->expectException(\Mustache\Exception\InvalidArgumentException::class);
        new \Mustache\Engine(array('escape' => $escape));
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
        $this->expectException(\Mustache\Exception\RuntimeException::class);
        $mustache = new \Mustache\Engine(array(
            'partials_loader' => new \Mustache\Loader\StringLoader(),
        ));

        $mustache->setPartials(array('foo' => '{{ foo }}'));
    }

    public function testMissingPartialsTreatedAsEmptyString()
    {
        $mustache = new \Mustache\Engine(array(
            'partials_loader' => new \Mustache\Loader\ArrayLoader(array(
                'foo' => 'FOO',
                'baz' => 'BAZ',
            )),
        ));

        $this->assertEquals('FOOBAZ', $mustache->render('{{>foo}}{{>bar}}{{>baz}}', array()));
    }

    public function testHelpers()
    {
        $foo = array($this, 'getFoo');
        $bar = 'BAR';
        $mustache = new \Mustache\Engine(array('helpers' => array(
            'foo' => $foo,
            'bar' => $bar,
        )));

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

        $baz = array($this, 'wrapWithUnderscores');
        $this->assertFalse($mustache->hasHelper('baz'));
        $this->assertFalse($helpers->has('baz'));

        $mustache->addHelper('baz', $baz);
        $this->assertTrue($mustache->hasHelper('baz'));
        $this->assertTrue($helpers->has('baz'));

        // ... and a functional test
        $tpl = $mustache->loadTemplate('{{foo}} - {{bar}} - {{#baz}}qux{{/baz}}');
        $this->assertEquals('foo - BAR - __qux__', $tpl->render());
        $this->assertEquals('foo - BAR - __qux__', $tpl->render(array('qux' => "won't mess things up")));
    }

    public static function wrapWithUnderscores($text)
    {
        return '__' . $text . '__';
    }

    public function testSetHelpersThrowsExceptions()
    {
        $this->expectException(\Mustache\Exception\InvalidArgumentException::class);
        $mustache = new \Mustache\Engine();
        $mustache->setHelpers('monkeymonkeymonkey');
    }

    public function testSetLoggerThrowsExceptions()
    {
        $this->expectException(\Mustache\Exception\InvalidArgumentException::class);
        $mustache = new \Mustache\Engine();
        $mustache->setLogger(new \StdClass());
    }

    public function testLoadPartialCascading()
    {
        $loader = new \Mustache\Loader\ArrayLoader(array(
            'foo' => 'FOO',
        ));

        $mustache = new \Mustache\Engine(array('loader' => $loader));

        $tpl = $mustache->loadTemplate('foo');

        $this->assertSame($tpl, $mustache->loadPartial('foo'));

        $mustache->setPartials(array(
            'foo' => 'f00',
        ));

        // setting partials overrides the default template loading fallback.
        $this->assertNotSame($tpl, $mustache->loadPartial('foo'));

        // but it didn't overwrite the original template loader templates.
        $this->assertSame($tpl, $mustache->loadTemplate('foo'));
    }

    public function testPartialLoadFailLogging()
    {
        $name     = tempnam(sys_get_temp_dir(), 'mustache-test');
        $mustache = new \Mustache\Engine(array(
            'logger'   => new \Mustache\Logger\StreamLogger($name, \Mustache\Logger::WARNING),
            'partials' => array(
                'foo' => 'FOO',
                'bar' => 'BAR',
            ),
        ));

        $result = $mustache->render('{{> foo }}{{> bar }}{{> baz }}', array());
        $this->assertEquals('FOOBAR', $result);

        $this->assertStringContainsString('WARNING: Partial not found: "baz"', file_get_contents($name));
    }

    public function testCacheWarningLogging()
    {
        list($name, $mustache) = $this->getLoggedMustache(\Mustache\Logger::WARNING);
        $mustache->render('{{ foo }}', array('foo' => 'FOO'));
        $this->assertStringContainsString('WARNING: Template cache disabled, evaluating', file_get_contents($name));
    }

    public function testLoggingIsNotTooAnnoying()
    {
        list($name, $mustache) = $this->getLoggedMustache();
        $mustache->render('{{ foo }}{{> bar }}', array('foo' => 'FOO'));
        $this->assertEmpty(file_get_contents($name));
    }

    public function testVerboseLoggingIsVerbose()
    {
        list($name, $mustache) = $this->getLoggedMustache(\Mustache\Logger::DEBUG);
        $mustache->render('{{ foo }}{{> bar }}', array('foo' => 'FOO'));
        $log = file_get_contents($name);
        $this->assertStringContainsString('DEBUG: Instantiating template: ', $log);
        $this->assertStringContainsString('WARNING: Partial not found: "bar"', $log);
    }

    public function testUnknownPragmaThrowsException()
    {
        $this->expectException(\Mustache\Exception\InvalidArgumentException::class);
        new \Mustache\Engine(array(
            'pragmas' => array('UNKNOWN'),
        ));
    }

    public function testCompileFromMustacheSourceInstance()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../fixtures/templates');
        $mustache = new \Mustache\Engine(array(
            'loader' => new \Mustache\Loader\ProductionFilesystemLoader($baseDir),
        ));
        $this->assertEquals('one contents', $mustache->render('one'));
    }

    private function getLoggedMustache($level = \Mustache\Logger::ERROR)
    {
        $name     = tempnam(sys_get_temp_dir(), 'mustache-test');
        $mustache = new \Mustache\Engine(array(
            'logger' => new \Mustache\Logger\StreamLogger($name, $level),
        ));

        return array($name, $mustache);
    }

    public function testCustomDelimiters()
    {
        $mustache = new \Mustache\Engine(array(
            'delimiters' => '[[ ]]',
            'partials'   => array(
                'one' => '[[> two ]]',
                'two' => '[[ a ]]',
            ),
        ));

        $tpl = $mustache->loadTemplate('[[# a ]][[ b ]][[/a ]]');
        $this->assertEquals('c', $tpl->render(array('a' => true, 'b' => 'c')));

        $tpl = $mustache->loadTemplate('[[> one ]]');
        $this->assertEquals('b', $tpl->render(array('a' => 'b')));
    }
}

class MustacheStub extends \Mustache\Engine
{
    public $source;
    public $template;

    public function loadTemplate($source)
    {
        $this->source = $source;

        return $this->template;
    }

    public function getProtectedLambdaCache()
    {
        return $this->getLambdaCache();
    }
}
