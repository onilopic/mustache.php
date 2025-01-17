<?php

declare(strict_types=1);

namespace Mustache;

use ArrayAccess;
use Closure;
use InvalidArgumentException;

/**
 * Mustache Template rendering Context.
 */
class Context
{
    private array $stack = [];
    private array $blockStack = [];

    /**
     * Mustache rendering Context constructor.
     *
     * @param mixed $context Default rendering context (default: null)
     */
    public function __construct(mixed $context = null)
    {
        if ($context !== null) {
            $this->stack = [$context];
        }
    }

    /**
     * Push a new Context frame onto the stack.
     *
     * @param mixed $value Object or array to use for context
     */
    public function push(mixed $value): void
    {
        $this->stack[] = $value;
    }

    /**
     * Push a new Context frame onto the block context stack.
     *
     * @param mixed $value Object or array to use for block context
     */
    public function pushBlockContext(mixed $value): void
    {
        $this->blockStack[] = $value;
    }

    /**
     * Pop the last Context frame from the stack.
     *
     * @return mixed Last Context frame (object or array)
     */
    public function pop(): mixed
    {
        return array_pop($this->stack);
    }

    /**
     * Pop the last block Context frame from the stack.
     *
     * @return mixed Last block Context frame (object or array)
     */
    public function popBlockContext(): mixed
    {
        return array_pop($this->blockStack);
    }

    /**
     * Get the last Context frame.
     *
     * @return mixed Last Context frame (object or array)
     */
    public function last(): mixed
    {
        return end($this->stack);
    }

    /**
     * Find a variable in the Context stack.
     *
     * Starting with the last Context frame (the context of the innermost section), and working back to the top-level
     * rendering context, look for a variable with the given name:
     *
     *  * If the Context frame is an associative array which contains the key $id, returns the value of that element.
     *  * If the Context frame is an object, this will check first for a public method, then a public property named
     *    $id. Failing both of these, it will try `__isset` and `__get` magic methods.
     *  * If a value named $id is not found in any Context frame, returns an empty string.
     *
     * @param string $id Variable name
     *
     * @return mixed Variable value, or '' if not found
     */
    public function find(string $id): mixed
    {
        return $this->findVariableInStack($id, $this->stack);
    }

    /**
     * Find a 'dot notation' variable in the Context stack.
     *
     * Note that dot notation traversal bubbles through scope differently than the regular find method. After finding
     * the initial chunk of the dotted name, each subsequent chunk is searched for only within the value of the previous
     * result. For example, given the following context stack:
     *
     *     $data = array(
     *         'name' => 'Fred',
     *         'child' => array(
     *             'name' => 'Bob'
     *         ),
     *     );
     *
     * ... and the Mustache following template:
     *
     *     {{ child.name }}
     *
     * ... the `name` value is only searched for within the `child` value of the global Context, not within parent
     * Context frames.
     *
     * @param string $id Dotted variable selector
     *
     * @return mixed Variable value, or '' if not found
     */
    public function findDot(string $id): mixed
    {
        $chunks = explode('.', $id);
        $first = array_shift($chunks);
        $value = $this->findVariableInStack($first, $this->stack);

        foreach ($chunks as $chunk) {
            if ($value === '') {
                return $value;
            }

            $value = $this->findVariableInStack($chunk, [$value]);
        }

        return $value;
    }

    /**
     * Find an 'anchored dot notation' variable in the Context stack.
     *
     * This is the same as findDot(), except it looks in the top of the context
     * stack for the first value, rather than searching the whole context stack
     * and starting from there.
     *
     * @param string $id Dotted variable selector
     *
     * @return mixed Variable value, or '' if not found
     * @throws InvalidArgumentException if given an invalid anchored dot $id
     *
     * @see \Mustache\Context::findDot
     *
     */
    public function findAnchoredDot(string $id): mixed
    {
        $chunks = explode('.', $id);
        $first = array_shift($chunks);
        if ($first !== '') {
            throw new InvalidArgumentException(sprintf('Unexpected id for findAnchoredDot: %s', $id));
        }

        $value = $this->last();

        foreach ($chunks as $chunk) {
            if ($value === '') {
                return $value;
            }

            $value = $this->findVariableInStack($chunk, [$value]);
        }

        return $value;
    }

    /**
     * Find an argument in the block context stack.
     *
     * @param string $id
     *
     * @return mixed Variable value, or '' if not found
     */
    public function findInBlock(string $id): mixed
    {
        foreach ($this->blockStack as $context) {
            if (array_key_exists($id, $context)) {
                return $context[$id];
            }
        }

        return '';
    }

    /**
     * Helper function to find a variable in the Context stack.
     *
     * @param string $id Variable name
     * @param array $stack Context stack
     *
     * @return mixed Variable value, or '' if not found
     * @see \Mustache\Context::find
     *
     */
    private function findVariableInStack(string $id, array $stack): mixed
    {
        for ($i = count($stack) - 1; $i >= 0; $i--) {
            $frame = &$stack[$i];

            switch (gettype($frame)) {
                case 'object':
                    if (!($frame instanceof Closure)) {
                        // Note that is_callable() *will not work here*
                        // See https://github.com/bobthecow/mustache.php/wiki/Magic-Methods
                        if (method_exists($frame, $id)) {
                            return $frame->$id();
                        }

                        if (isset($frame->$id)) {
                            return $frame->$id;
                        }

                        if ($frame instanceof ArrayAccess && isset($frame[$id])) {
                            return $frame[$id];
                        }
                    }
                    break;

                case 'array':
                    if (array_key_exists($id, $frame)) {
                        return $frame[$id];
                    }
                    break;
            }
        }

        return '';
    }
}
