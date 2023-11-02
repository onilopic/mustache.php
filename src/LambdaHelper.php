<?php declare(strict_types=1);

namespace Mustache;

/**
 * Mustache Lambda Helper.
 *
 * Passed as the second argument to section lambdas (higher order sections),
 * giving them access to a `render` method for rendering a string with the
 * current context.
 */
class LambdaHelper
{
    private Engine $mustache;
    private Context $context;
    private string $delims;

    /**
     * Mustache Lambda Helper constructor.
     *
     * @param Engine $mustache Mustache engine instance
     * @param Context $context Rendering context
     * @param string $delims Optional custom delimiters, in the format `{{= <% %> =}}`. (default: null)
     */
    public function __construct(Engine $mustache, Context $context, string $delims = '')
    {
        $this->mustache = $mustache;
        $this->context = $context;
        $this->delims = $delims;
    }

    /**
     * Render a string as a Mustache template with the current rendering context.
     *
     * @param string $string
     *
     * @return string Rendered template
     */
    public function render(string $string): string
    {
        return $this->mustache
            ->loadLambda($string, $this->delims)
            ->renderInternal($this->context);
    }

    /**
     * Render a string as a Mustache template with the current rendering context.
     *
     * @param string $string
     *
     * @return string Rendered template
     */
    public function __invoke(string $string): string
    {
        return $this->render($string);
    }

    /**
     * Get a Lambda Helper with custom delimiters.
     *
     * @param string $delimiters Custom delimiters, in the format `{{= <% %> =}}`
     *
     * @return LambdaHelper
     */
    public function withDelimiters(string $delimiters): self
    {
        return new self($this->mustache, $this->context, $delimiters);
    }
}
