<?php

declare(strict_types=1);

namespace Mustache\Loader;

use Mustache\Contract\Loader;
use Mustache\Exception\InvalidArgumentException;
use Mustache\Exception\UnknownTemplateException;

/**
 * A Mustache Template loader for inline templates.
 *
 * With the InlineLoader, templates can be defined at the end of any PHP source
 * file:
 *
 *     $loader  = new \Mustache\Contract\Loader\InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__);
 *     $hello   = $loader->load('hello');
 *     $goodbye = $loader->load('goodbye');
 *
 *     __halt_compiler();
 *
 *     @@ hello
 *     Hello, {{ planet }}!
 *
 *     @@ goodbye
 *     Goodbye, cruel {{ planet }}
 *
 * Templates are deliniated by lines containing only `@@ name`.
 *
 * The InlineLoader is well-suited to micro-frameworks such as Silex:
 *
 *     $app->register(new MustacheServiceProvider, array(
 *         'mustache.loader' => new \Mustache\Contract\Loader\InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__)
 *     ));
 *
 *     $app->get('/{name}', function ($name) use ($app) {
 *         return $app['mustache']->render('hello', compact('name'));
 *     })
 *     ->value('name', 'world');
 *
 *     // ...
 *
 *     __halt_compiler();
 *
 *     @@ hello
 *     Hello, {{ name }}!
 */
class InlineLoader implements Loader
{
    protected string $fileName;
    protected int $offset;
    protected $templates;

    /**
     * The InlineLoader requires a filename and offset to process templates.
     *
     * The magic constants `__FILE__` and `__COMPILER_HALT_OFFSET__` are usually
     * perfectly suited to the job:
     *
     *     $loader = new \Mustache\Contract\Loader\InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__);
     *
     * Note that this only works if the loader is instantiated inside the same
     * file as the inline templates. If the templates are located in another
     * file, it would be necessary to manually specify the filename and offset.
     *
     * @param string $fileName The file to parse for inline templates
     * @param int    $offset   A string offset for the start of the templates.
     *                         This usually coincides with the `__halt_compiler`
     *                         call, and the `__COMPILER_HALT_OFFSET__`
     */
    public function __construct(string $fileName, int $offset)
    {
        if (!is_file($fileName)) {
            throw new InvalidArgumentException('InlineLoader expects a valid filename.');
        }

        if ($offset < 0) {
            throw new InvalidArgumentException('InlineLoader expects a valid file offset.');
        }

        $this->fileName = $fileName;
        $this->offset   = $offset;
    }

    /**
     * Load a Template by name.
     *
     * @throws UnknownTemplateException If a template file is not found
     *
     * @param string $name
     *
     * @return string Mustache Template source
     */
    public function load(string $name): string
    {
        $this->loadTemplates();

        if (!array_key_exists($name, $this->templates)) {
            throw new UnknownTemplateException($name);
        }

        return $this->templates[$name];
    }

    /**
     * Parse and load templates from the end of a source file.
     */
    protected function loadTemplates()
    {
        if ($this->templates === null) {
            $this->templates = [];
            $data = file_get_contents($this->fileName, false, null, $this->offset);
            foreach (preg_split("/^@@(?= [\w\d.]+$)/m", $data, -1) as $chunk) {
                if (trim($chunk)) {
                    [$name, $content]         = explode("\n", $chunk, 2);
                    $this->templates[trim($name)] = trim($content);
                }
            }
        }
    }
}
