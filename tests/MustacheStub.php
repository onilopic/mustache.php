<?php

namespace Mustache\Tests;

use Mustache\Contract\Cache;
use Mustache\Engine;
use Mustache\Template;

class MustacheStub extends Engine
{
    public string $source;
    public Template $template;

    public function loadTemplate(string $source): Template
    {
        $this->source = $source;

        return $this->template;
    }

    public function getProtectedLambdaCache(): Cache
    {
        return $this->getLambdaCache();
    }
}