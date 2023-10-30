<?php

namespace Mustache\Tests;

class TemplateStub extends \Mustache\Template
{
    public $rendered;

    public function getMustache()
    {
        return $this->mustache;
    }

    public function renderInternal(\Mustache\Context $context, $indent = '', $escape = false)
    {
        return $this->rendered;
    }
}