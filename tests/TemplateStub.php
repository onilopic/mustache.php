<?php

namespace Mustache\Tests;

use Mustache\Context;
use Mustache\Engine;
use Mustache\Template;

class TemplateStub extends Template
{
    public string $rendered;

    public function getMustache(): Engine
    {
        return $this->mustache;
    }

    public function renderInternal(Context $context, $indent = '', $escape = false): string
    {
        return $this->rendered;
    }
}