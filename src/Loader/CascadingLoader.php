<?php

declare(strict_types=1);

namespace Mustache\Loader;

use Mustache\Contract\Loader;
use Mustache\Exception\UnknownTemplateException;

/**
 * A Mustache Template cascading loader implementation, which delegates to other
 * Loader instances.
 */
class CascadingLoader implements Loader
{
    private array $loaders = [];

    /**
     * Construct a CascadingLoader with an array of loaders.
     *
     *     $loader = new \Mustache\Contract\Loader\CascadingLoader(array(
     *         new \Mustache\Contract\Loader\InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__),
     *         new \Mustache\Contract\Loader\FilesystemLoader(__DIR__.'/templates')
     *     ));
     *
     * @param Loader[] $loaders
     */
    public function __construct(array $loaders = array())
    {
        //$this->loaders = array();
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    /**
     * Add a Loader instance.
     *
     * @param \Mustache\Contract\Loader $loader
     */
    public function addLoader(Loader $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * Load a Template by name.
     *
     * @param string $name
     *
     * @return string|\Mustache\Contract\Source Mustache Template source
     */
    public function load(string $name): string|\Mustache\Contract\Source
    {
        foreach ($this->loaders as $loader) {
            try {
                return $loader->load($name);
            } catch (UnknownTemplateException $e) {
                // do nothing, check the next loader.
            }
        }

        throw new UnknownTemplateException($name);
    }
}
