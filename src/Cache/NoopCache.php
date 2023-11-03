<?php

declare(strict_types=1);

namespace Mustache\Cache;

/**
 * Mustache Cache in-memory implementation.
 *
 * The in-memory cache is used for uncached lambda section templates. It's also useful during development, but is not
 * recommended for production use.
 */
class NoopCache extends AbstractCache
{
    /**
     * Loads nothing. Move along.
     *
     * @param string $key
     *
     * @return bool
     */
    public function load(string $key): bool
    {
        return false;
    }

    /**
     * Loads the compiled Mustache Template class without caching.
     *
     * @param string $key
     * @param string $value
     */
    public function cache($key, $value)
    {
        $this->log(
            \Mustache\Contract\Logger::WARNING,
            'Template cache disabled, evaluating "{className}" class at runtime',
            ['className' => $key]
        );
        eval('?>' . $value);
    }
}
