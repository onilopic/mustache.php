<?php

declare(strict_types=1);

namespace Mustache;

use Closure;
use Mustache\Cache\FilesystemCache;
use Mustache\Cache\NoopCache;
use Mustache\Contract\Cache;
use Mustache\Contract\Loader;
use Mustache\Contract\Logger;
use Mustache\Contract\MutableLoader;
use Mustache\Contract\Source;
use Mustache\Exception\InvalidArgumentException;
use Mustache\Exception\RuntimeException;
use Mustache\Exception\UnknownTemplateException;
use Mustache\Loader\ArrayLoader;
use Mustache\Loader\StringLoader;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Traversable;

/**
 * A Mustache implementation in PHP.
 *
 * {@link http://defunkt.github.com/mustache}
 *
 * Mustache is a framework-agnostic logic-less templating language. It enforces separation of view
 * logic from template files. In fact, it is not even possible to embed logic in the template.
 *
 * This is very, very rad.
 *
 * @author Justin Hileman {@link http://justinhileman.com}
 */
class Engine
{
    public final const VERSION = '2.14.2';
    //public final const SPEC_VERSION = '1.3.0';

    const PRAGMA_FILTERS = 'FILTERS';
    const PRAGMA_BLOCKS = 'BLOCKS';
    const PRAGMA_ANCHORED_DOT = 'ANCHORED-DOT';
    const PRAGMA_DYNAMIC_NAMES = 'DYNAMIC-NAMES';

    // Known pragmas
    private static array $knownPragmas = [
        self::PRAGMA_FILTERS => true,
        self::PRAGMA_BLOCKS => true,
        self::PRAGMA_ANCHORED_DOT => true,
        self::PRAGMA_DYNAMIC_NAMES => true,
    ];

    // Template cache
    private array $templates = [];

    // Environment
    private string $templateClassPrefix = '__Mustache_';
    private Cache $cache;
    private Cache $lambdaCache;
    private bool $cacheLambdaTemplates = false;
    private Loader $loader;
    private Loader $partialsLoader;
    private HelperCollection $helpers;
    private string|Closure $escape;
    private int $entityFlags = ENT_COMPAT;
    private string $charset = 'UTF-8';
    private Logger|PsrLoggerInterface|null $logger = null;
    private bool $strictCallables = false;
    private array $pragmas = [];
    private string $delimiters = '';

    // Services
    private Tokenizer $tokenizer;
    private Parser $parser;
    private Compiler $compiler;

    /**
     * Mustache class constructor.
     *
     * Passing an $options array allows overriding certain Mustache options during instantiation:
     *
     *     $options = array(
     *         // The class prefix for compiled templates. Defaults to '__Mustache_'.
     *         'template_class_prefix' => '__MyTemplates_',
     *
     *         // A Mustache cache instance or a cache directory string for compiled templates.
     *         // Mustache will not cache templates unless this is set.
     *         'cache' => dirname(__FILE__).'/tmp/cache/mustache',
     *
     *         // Override default permissions for cache files. Defaults to using the system-defined umask. It is
     *         // *strongly* recommended that you configure your umask properly rather than overriding permissions here.
     *         'cache_file_mode' => 0666,
     *
     *         // Optionally, enable caching for lambda section templates. This is generally not recommended, as lambda
     *         // sections are often too dynamic to benefit from caching.
     *         'cache_lambda_templates' => true,
     *
     *         // Customize the tag delimiters used by this engine instance. Note that overriding here changes the
     *         // delimiters used to parse all templates and partials loaded by this instance. To override just for a
     *         // single template, use an inline "change delimiters" tag at the start of the template file:
     *         //
     *         //     {{=<% %>=}}
     *         //
     *         'delimiters' => '<% %>',
     *
     *         // A Mustache template loader instance. Uses a StringLoader if not specified.
     *         'loader' => new \Mustache\Contract\Loader\FilesystemLoader(dirname(__FILE__).'/views'),
     *
     *         // A Mustache loader instance for partials.
     *         'partials_loader' => new \Mustache\Contract\Loader\FilesystemLoader(dirname(__FILE__).'/views/partials'),
     *
     *         // An array of Mustache partials. Useful for quick-and-dirty string template loading, but not as
     *         // efficient or lazy as a Filesystem (or database) loader.
     *         'partials' => array('foo' => file_get_contents(dirname(__FILE__).'/views/partials/foo.mustache')),
     *
     *         // An array of 'helpers'. Helpers can be global variables or objects, closures (e.g. for higher order
     *         // sections), or any other valid Mustache context value. They will be prepended to the context stack,
     *         // so they will be available in any template loaded by this Mustache instance.
     *         'helpers' => array('i18n' => function ($text) {
     *             // do something translate here...
     *         }),
     *
     *         // An 'escape' callback, responsible for escaping double-mustache variables.
     *         'escape' => function ($value) {
     *             return htmlspecialchars($buffer, ENT_COMPAT, 'UTF-8');
     *         },
     *
     *         // Type argument for `htmlspecialchars`.  Defaults to ENT_COMPAT.  You may prefer ENT_QUOTES.
     *         'entity_flags' => ENT_QUOTES,
     *
     *         // Character set for `htmlspecialchars`. Defaults to 'UTF-8'. Use 'UTF-8'.
     *         'charset' => 'ISO-8859-1',
     *
     *         // A Mustache Logger instance. No logging will occur unless this is set. Using a PSR-3 compatible
     *         // logging library -- such as Monolog -- is highly recommended. A simple stream logger implementation is
     *         // available as well:
     *         'logger' => new \Mustache\Logger_StreamLogger('php://stderr'),
     *
     *         // Only treat Closure instances and invokable classes as callable. If true, values like
     *         // `array('ClassName', 'methodName')` and `array($classInstance, 'methodName')`, which are traditionally
     *         // "callable" in PHP, are not called to resolve variables for interpolation or section contexts. This
     *         // helps protect against arbitrary code execution when user input is passed directly into the template.
     *         // This currently defaults to false, but will default to true in v3.0.
     *         'strict_callables' => true,
     *
     *         // Enable pragmas across all templates, regardless of the presence of pragma tags in the individual
     *         // templates.
     *         'pragmas' => [\Mustache\Engine::PRAGMA_FILTERS],
     *     );
     *
     * @param array $options (default: array())
     * @throws InvalidArgumentException If `escape` option is not callable
     *
     */
    public function __construct(array $options = [])
    {
        if (isset($options['template_class_prefix'])) {
            if ((string)$options['template_class_prefix'] === '') {
                throw new InvalidArgumentException('Mustache Constructor "template_class_prefix" must not be empty');
            }

            $this->templateClassPrefix = $options['template_class_prefix'];
        }

        if (isset($options['cache'])) {
            $cache = $options['cache'];

            if (is_string($cache)) {
                $mode = $options['cache_file_mode'] ?? null;
                $cache = new FilesystemCache($cache, $mode);
            }

            $this->setCache($cache);
        }

        if (isset($options['cache_lambda_templates'])) {
            $this->cacheLambdaTemplates = (bool)$options['cache_lambda_templates'];
        }

        if (isset($options['loader'])) {
            $this->setLoader($options['loader']);
        }

        if (isset($options['partials_loader'])) {
            $this->setPartialsLoader($options['partials_loader']);
        }

        if (isset($options['partials'])) {
            $this->setPartials($options['partials']);
        }

        if (isset($options['helpers'])) {
            $this->setHelpers($options['helpers']);
        }

        if (isset($options['escape'])) {
            if (!is_callable($options['escape'])) {
                throw new InvalidArgumentException('Mustache Constructor "escape" option must be callable');
            }

            $this->escape = $options['escape'];
        }

        if (isset($options['entity_flags'])) {
            $this->entityFlags = $options['entity_flags'];
        }

        if (isset($options['charset'])) {
            $this->charset = $options['charset'];
        }

        if (isset($options['logger'])) {
            $this->setLogger($options['logger']);
        }

        if (isset($options['strict_callables'])) {
            $this->strictCallables = $options['strict_callables'];
        }

        if (isset($options['delimiters'])) {
            $this->delimiters = $options['delimiters'];
        }

        if (isset($options['pragmas'])) {
            foreach ($options['pragmas'] as $pragma) {
                if (!isset(self::$knownPragmas[$pragma])) {
                    throw new InvalidArgumentException(sprintf('Unknown pragma: "%s".', $pragma));
                }
                $this->pragmas[$pragma] = true;
            }
        }
    }

    /**
     * Shortcut 'render' invocation.
     *
     * Equivalent to calling `$mustache->loadTemplate($template)->render($context);`
     *
     * @param string $template
     * @param mixed $context (default: array())
     *
     * @return string Rendered template
     * @see \Mustache\Template::render
     *
     * @see \Mustache\Engine::loadTemplate
     */
    public function render(string $template, mixed $context = array()): string
    {
        return $this->loadTemplate($template)->render($context);
    }

    /**
     * Get the current Mustache escape callback.
     *
     * @return callable|null
     */
    public function getEscape(): ?callable
    {
        return $this->escape;
    }

    /**
     * Get the current Mustache entity type to escape.
     *
     * @return int
     */
    public function getEntityFlags(): int
    {
        return $this->entityFlags;
    }

    /**
     * Get the current Mustache character set.
     *
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * Get the current globally enabled pragmas.
     *
     * @return array
     */
    public function getPragmas(): array
    {
        return array_keys($this->pragmas);
    }

    /**
     * Set the Mustache template Loader instance.
     *
     * @param Loader $loader
     */
    public function setLoader(Loader $loader): void
    {
        $this->loader = $loader;
    }

    /**
     * Get the current Mustache template Loader instance.
     *
     * If no Loader instance has been explicitly specified, this method will instantiate and return
     * a StringLoader instance.
     *
     * @return Loader
     */
    public function getLoader(): Loader
    {
        if (!isset($this->loader)) {
            $this->loader = new StringLoader();
        }

        return $this->loader;
    }

    /**
     * Set the Mustache partials Loader instance.
     *
     * @param Loader $partialsLoader
     */
    public function setPartialsLoader(Loader $partialsLoader): void
    {
        $this->partialsLoader = $partialsLoader;
    }

    /**
     * Get the current Mustache partials Loader instance.
     *
     * If no Loader instance has been explicitly specified, this method will instantiate and return
     * an ArrayLoader instance.
     *
     * @return Loader
     */
    public function getPartialsLoader(): Loader
    {
        if (!isset($this->partialsLoader)) {
            $this->partialsLoader = new ArrayLoader();
        }

        return $this->partialsLoader;
    }

    /**
     * Set partials for the current partials Loader instance.
     *
     * @param array $partials (default: array())
     * @throws RuntimeException If the current Loader instance is immutable
     *
     */
    public function setPartials(array $partials = []): void
    {
        if (!isset($this->partialsLoader)) {
            $this->partialsLoader = new ArrayLoader();
        }

        if (!$this->partialsLoader instanceof MutableLoader) {
            throw new RuntimeException('Unable to set partials on an immutable Mustache Loader instance');
        }

        $this->partialsLoader->setTemplates($partials);
    }

    /**
     * Set an array of Mustache helpers.
     *
     * An array of 'helpers'. Helpers can be global variables or objects, closures (e.g. for higher order sections), or
     * any other valid Mustache context value. They will be prepended to the context stack, so they will be available in
     * any template loaded by this Mustache instance.
     *
     * @param iterable $helpers
     * @throws InvalidArgumentException if $helpers is not an array or Traversable
     *
     */
    public function setHelpers(iterable $helpers): void
    {
        if (!is_array($helpers) && !$helpers instanceof Traversable) {
            throw new InvalidArgumentException('setHelpers expects an array of helpers');
        }

        $this->getHelpers()->clear();

        foreach ($helpers as $name => $helper) {
            $this->addHelper($name, $helper);
        }
    }

    /**
     * Get the current set of Mustache helpers.
     *
     * @return HelperCollection
     * @see Engine::setHelpers
     *
     */
    public function getHelpers(): HelperCollection
    {
        if (!isset($this->helpers)) {
            $this->helpers = new HelperCollection();
        }

        return $this->helpers;
    }

    /**
     * Add a new Mustache helper.
     *
     * @param string $name
     * @param mixed $helper
     * @see \Mustache\Engine::setHelpers
     *
     */
    public function addHelper(string $name, mixed $helper): void
    {
        $this->getHelpers()->add($name, $helper);
    }

    /**
     * Get a Mustache helper by name.
     *
     * @param string $name
     *
     * @return mixed Helper
     * @see \Mustache\Engine::setHelpers
     *
     */
    public function getHelper(string $name): mixed
    {
        return $this->getHelpers()->get($name);
    }

    /**
     * Check whether this Mustache instance has a helper.
     *
     * @param string $name
     *
     * @return bool True if the helper is present
     * @see \Mustache\Engine::setHelpers
     *
     */
    public function hasHelper(string $name): bool
    {
        return $this->getHelpers()->has($name);
    }

    /**
     * Remove a helper by name.
     *
     * @param string $name
     * @see \Mustache\Engine::setHelpers
     *
     */
    public function removeHelper(string $name): void
    {
        $this->getHelpers()->remove($name);
    }

    /**
     * Set the Mustache Logger instance.
     *
     * @param ?Logger|PsrLoggerInterface $logger
     * @throws InvalidArgumentException If logger is not an instance of \Mustache\Contract\Logger or Psr\Log\LoggerInterface
     *
     */
    public function setLogger(null|Logger|PsrLoggerInterface $logger = null): void
    {
        if ($logger !== null && !($logger instanceof Logger || is_a($logger, 'Psr\\Log\\LoggerInterface'))) {
            throw new InvalidArgumentException('Expected an instance of \Mustache\Logger or Psr\\Log\\LoggerInterface.');
        }

        if ($this->getCache()->getLogger() === null) {
            $this->getCache()->setLogger($logger);
        }

        $this->logger = $logger;
    }

    /**
     * Get the current Mustache Logger instance.
     *
     * @return null|Logger|PsrLoggerInterface
     */
    public function getLogger(): Logger|PsrLoggerInterface|null
    {
        return $this->logger;
    }

    /**
     * Set the Mustache Tokenizer instance.
     *
     * @param Tokenizer $tokenizer
     */
    public function setTokenizer(Tokenizer $tokenizer): void
    {
        $this->tokenizer = $tokenizer;
    }

    /**
     * Get the current Mustache Tokenizer instance.
     *
     * If no Tokenizer instance has been explicitly specified, this method will instantiate and return a new one.
     *
     * @return Tokenizer
     */
    public function getTokenizer(): Tokenizer
    {
        if (!isset($this->tokenizer)) {
            $this->tokenizer = new Tokenizer();
        }

        return $this->tokenizer;
    }

    /**
     * Set the Mustache Parser instance.
     *
     * @param Parser $parser
     */
    public function setParser(Parser $parser): void
    {
        $this->parser = $parser;
    }

    /**
     * Get the current Mustache Parser instance.
     *
     * If no Parser instance has been explicitly specified, this method will instantiate and return a new one.
     *
     * @return Parser
     */
    public function getParser(): Parser
    {
        if (!isset($this->parser)) {
            $this->parser = new Parser();
        }

        return $this->parser;
    }

    /**
     * Set the Mustache Compiler instance.
     *
     * @param Compiler $compiler
     */
    public function setCompiler(Compiler $compiler): void
    {
        $this->compiler = $compiler;
    }

    /**
     * Get the current Mustache Compiler instance.
     *
     * If no Compiler instance has been explicitly specified, this method will instantiate and return a new one.
     *
     * @return Compiler
     */
    public function getCompiler(): Compiler
    {
        if (!isset($this->compiler)) {
            $this->compiler = new Compiler();
        }

        return $this->compiler;
    }

    /**
     * Set the Mustache Cache instance.
     *
     * @param Cache $cache
     */
    public function setCache(Cache $cache): void
    {
        if (isset($this->logger) && $cache->getLogger() === null) {
            $cache->setLogger($this->getLogger());
        }

        $this->cache = $cache;
    }

    /**
     * Get the current Mustache Cache instance.
     *
     * If no Cache instance has been explicitly specified, this method will instantiate and return a new one.
     *
     * @return Cache
     */
    public function getCache(): Cache
    {
        if (!isset($this->cache)) {
            $this->setCache(new NoopCache());
        }

        return $this->cache;
    }

    /**
     * Get the current Lambda Cache instance.
     *
     * If 'cache_lambda_templates' is enabled, this is the default cache instance. Otherwise, it is a NoopCache.
     *
     * @return Cache
     * @see \Mustache\Engine::getCache
     *
     */
    protected function getLambdaCache(): Cache
    {
        if ($this->cacheLambdaTemplates) {
            return $this->getCache();
        }

        if (!isset($this->lambdaCache)) {
            $this->lambdaCache = new NoopCache();
        }

        return $this->lambdaCache;
    }

    /**
     * Helper method to generate a Mustache template class.
     *
     * This method must be updated any time options are added which make it so
     * the same template could be parsed and compiled multiple different ways.
     *
     * @param string|Source $source
     *
     * @return string Mustache Template class name
     */
    public function getTemplateClassName(string|Source $source): string
    {
        // For the most part, adding a new option here should do the trick.
        //
        // Pick a value here which is unique for each possible way the template
        // could be compiled... but not necessarily unique per option value. See
        // escape below, which only needs to differentiate between 'custom' and
        // 'default' escapes.
        //
        // Keep this list in alphabetical order :)
        $chunks = [
            'charset' => $this->charset,
            'delimiters' => $this->delimiters ?: '{{ }}',
            'entityFlags' => $this->entityFlags,
            'escape' => isset($this->escape) ? 'custom' : 'default',
            'key' => ($source instanceof Source) ? $source->getKey() : 'source',
            'pragmas' => $this->getPragmas(),
            'strictCallables' => $this->strictCallables,
            'version' => self::VERSION,
        ];

        $key = json_encode($chunks);

        // Template Source instances have already provided their own source key. For strings, just include the whole
        // source string in the md5 hash.
        if (!$source instanceof Source) {
            $key .= "\n" . $source;
        }

        return $this->templateClassPrefix . md5($key);
    }

    /**
     * Load a Mustache Template by name.
     *
     * @param string $name
     *
     * @return Template
     */
    public function loadTemplate(string $name): Template
    {
        return $this->loadSource($this->getLoader()->load($name));
    }

    /**
     * Load a Mustache partial Template by name.
     *
     * This is a helper method used internally by Template instances for loading partial templates. You can most likely
     * ignore it completely.
     *
     * @param string $name
     *
     * @return ?Template
     */
    public function loadPartial(string $name): ?Template
    {
        try {
            if (isset($this->partialsLoader)) {
                $loader = $this->partialsLoader;
            } elseif (isset($this->loader) && !$this->loader instanceof StringLoader) {
                $loader = $this->loader;
            } else {
                throw new UnknownTemplateException($name);
            }

            return $this->loadSource($loader->load($name));
        } catch (UnknownTemplateException $e) {
            // If the named partial cannot be found, log then return null.
            $this->log(
                Logger::WARNING,
                'Partial not found: "{name}"',
                array('name' => $e->getTemplateName())
            );
        }

        return null;
    }

    /**
     * Load a Mustache lambda Template by source.
     *
     * This is a helper method used by Template instances to generate subtemplates for Lambda sections. You can most
     * likely ignore it completely.
     *
     * @param string $source
     * @param string $delims (default: null)
     *
     * @return Template
     */
    public function loadLambda(string $source, string $delims = ''): Template
    {
        if ($delims !== '') {
            $source = $delims . "\n" . $source;
        }

        return $this->loadSource($source, $this->getLambdaCache());
    }

    /**
     * Instantiate and return a Mustache Template instance by source.
     *
     * Optionally provide a \Mustache\Contract\Cache instance. This is used internally by \Mustache\Engine::loadLambda to respect
     * the 'cache_lambda_templates' configuration option.
     *
     * @param string|Source $source
     * @param ?Cache $cache (default: null)
     *
     * @return Template
     * @see \Mustache\Engine::loadTemplate
     * @see \Mustache\Engine::loadPartial
     * @see \Mustache\Engine::loadLambda
     *
     */
    private function loadSource(string|Source $source, Cache $cache = null): Template
    {
        $className = $this->getTemplateClassName($source);

        if (!isset($this->templates[$className])) {
            if ($cache === null) {
                $cache = $this->getCache();
            }

            if (!class_exists($className, false)) {
                if (!$cache->load($className)) {
                    $compiled = $this->compile($source);
                    $cache->cache($className, $compiled);
                }
            }

            $this->log(
                Logger::DEBUG,
                'Instantiating template: "{className}"',
                array('className' => $className)
            );

            $this->templates[$className] = new $className($this);
        }

        return $this->templates[$className];
    }

    /**
     * Helper method to tokenize a Mustache template.
     *
     * @param string $source
     *
     * @return array Tokens
     * @see Tokenizer::scan
     *
     */
    private function tokenize(string $source): array
    {
        return $this->getTokenizer()->scan($source, $this->delimiters);
    }

    /**
     * Helper method to parse a Mustache template.
     *
     * @param string $source
     *
     * @return array Token tree
     * @see \Mustache\Parser::parse
     *
     */
    private function parse(string $source): array
    {
        $parser = $this->getParser();
        $parser->setPragmas($this->getPragmas());

        return $parser->parse($this->tokenize($source));
    }

    /**
     * Helper method to compile a Mustache template.
     *
     * @param string|Source $source
     *
     * @return string generated Mustache template class code
     * @see \Mustache\Compiler::compile
     *
     */
    private function compile(string|Source $source): string
    {
        $name = $this->getTemplateClassName($source);

        $this->log(
            Logger::INFO,
            'Compiling template to "{className}" class',
            array('className' => $name)
        );

        if ($source instanceof Source) {
            $source = $source->getSource();
        }
        $tree = $this->parse($source);

        $compiler = $this->getCompiler();
        $compiler->setPragmas($this->getPragmas());

        return $compiler->compile($source, $tree, $name, isset($this->escape), $this->charset, $this->strictCallables, $this->entityFlags);
    }

    /**
     * Add a log record if logging is enabled.
     *
     * @param int|string $level The logging level
     * @param string $message The log message
     * @param array $context The log context
     */
    // @todo check actual level type
    private function log(int|string $level, string $message, array $context = array()): void
    {
        if (isset($this->logger)) {
            $this->logger->log($level, $message, $context);
        }
    }
}
