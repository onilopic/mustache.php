<?php

declare(strict_types=1);

namespace Mustache\Contract;

use Mustache\Exception\UnknownTemplateException;

/**
 * Mustache Template Loader interface.
 */
interface Loader
{
    /**
     * Load a Template by name.
     *
     * @param string $name
     *
     * @return string|Source Mustache Template source
     * @throws UnknownTemplateException If a template file is not found
     *
     */
    public function load(string $name): string|Source;
}
