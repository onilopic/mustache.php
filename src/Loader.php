<?php

namespace Mustache;

/**
 * Mustache Template Loader interface.
 */
interface Loader
{
    /**
     * Load a Template by name.
     *
     * @throws \Mustache\Exception\UnknownTemplateException If a template file is not found
     *
     * @param string $name
     *
     * @return string|\Mustache\Source Mustache Template source
     */
    public function load($name);
}
