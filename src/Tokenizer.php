<?php

declare(strict_types=1);

namespace Mustache;

use Mustache\Exception\InvalidArgumentException;
use Mustache\Exception\SyntaxException;

/**
 * Mustache Tokenizer class.
 *
 * This class is responsible for turning raw template source into a set of Mustache tokens.
 */
class Tokenizer
{
    // Finite state machine states
    public const IN_TEXT     = 0;
    public const IN_TAG_TYPE = 1;
    public const IN_TAG      = 2;

    // Token types
    public const T_SECTION      = '#';
    public const T_INVERTED     = '^';
    public const T_END_SECTION  = '/';
    public const T_COMMENT      = '!';
    public const T_PARTIAL      = '>';
    public const T_PARENT       = '<';
    public const T_DELIM_CHANGE = '=';
    public const T_ESCAPED      = '_v';
    public const T_UNESCAPED    = '{';
    public const T_UNESCAPED_2  = '&';
    public const T_TEXT         = '_t';
    public const T_PRAGMA       = '%';
    public const T_BLOCK_VAR    = '$';
    public const T_BLOCK_ARG    = '$arg';

    // Valid token types
    private static array $tagTypes = [
        self::T_SECTION      => true,
        self::T_INVERTED     => true,
        self::T_END_SECTION  => true,
        self::T_COMMENT      => true,
        self::T_PARTIAL      => true,
        self::T_PARENT       => true,
        self::T_DELIM_CHANGE => true,
        self::T_ESCAPED      => true,
        self::T_UNESCAPED    => true,
        self::T_UNESCAPED_2  => true,
        self::T_PRAGMA       => true,
        self::T_BLOCK_VAR    => true,
    ];

    private static array $tagNames = [
        self::T_SECTION      => 'section',
        self::T_INVERTED     => 'inverted section',
        self::T_END_SECTION  => 'section end',
        self::T_COMMENT      => 'comment',
        self::T_PARTIAL      => 'partial',
        self::T_PARENT       => 'parent',
        self::T_DELIM_CHANGE => 'set delimiter',
        self::T_ESCAPED      => 'variable',
        self::T_UNESCAPED    => 'unescaped variable',
        self::T_UNESCAPED_2  => 'unescaped variable',
        self::T_PRAGMA       => 'pragma',
        self::T_BLOCK_VAR    => 'block variable',
        self::T_BLOCK_ARG    => 'block variable',
    ];

    // Token properties
    public const TYPE    = 'type';
    public const NAME    = 'name';
    public const DYNAMIC = 'dynamic';
    public const OTAG    = 'otag';
    public const CTAG    = 'ctag';
    public const LINE    = 'line';
    public const INDEX   = 'index';
    public const END     = 'end';
    public const INDENT  = 'indent';
    public const NODES   = 'nodes';
    public const VALUE   = 'value';
    public const FILTERS = 'filters';

    private int $state;
    private ?string $tagType;
    private string $buffer;
    private array $tokens;
    private bool|int $seenTag;
    private int $line;

    private string $otag;
    private string $otagChar;
    private int $otagLen;

    private string $ctag;
    private string $ctagChar;
    private int $ctagLen;

    /**
     * Scan and tokenize template source.
     *
     * @throws SyntaxException when mismatched section tags are encountered
     * @throws InvalidArgumentException when $delimiters string is invalid
     *
     * @param string $text       Mustache template source to tokenize
     * @param int|string|null $delimiters Optionally, pass initial opening and closing delimiters
     * (default: empty string)
     *
     * @return array Set of Mustache tokens
     */
    public function scan(string $text, int|string|null $delimiters = ''): array
    {
        $this->reset();

        if (is_string($delimiters) && $delimiters = trim($delimiters)) {
            $this->setDelimiters($delimiters);
        }

        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            switch ($this->state) {
                case self::IN_TEXT:
                    $char = $text[$i];
                    // Test whether it's time to change tags.
                    if ($char === $this->otagChar && substr($text, $i, $this->otagLen) === $this->otag) {
                        $i--;
                        $this->flushBuffer();
                        $this->state = self::IN_TAG_TYPE;
                    } else {
                        $this->buffer .= $char;
                        if ($char === "\n") {
                            $this->flushBuffer();
                            $this->line++;
                        }
                    }
                    break;

                case self::IN_TAG_TYPE:
                    $i += $this->otagLen - 1;
                    $char = $text[$i + 1];
                    if (isset(self::$tagTypes[$char])) {
                        $tag = $char;
                        $this->tagType = $tag;
                    } else {
                        $tag = null;
                        $this->tagType = self::T_ESCAPED;
                    }

                    if ($this->tagType === self::T_DELIM_CHANGE) {
                        $i = $this->changeDelimiters($text, $i);
                        $this->state = self::IN_TEXT;
                    } elseif ($this->tagType === self::T_PRAGMA) {
                        $i = $this->addPragma($text, $i);
                        $this->state = self::IN_TEXT;
                    } else {
                        if ($tag !== null) {
                            $i++;
                        }
                        $this->state = self::IN_TAG;
                    }
                    $this->seenTag = $i;
                    break;

                default:
                    $char = $text[$i];
                    // Test whether it's time to change tags.
                    if ($char === $this->ctagChar && substr($text, $i, $this->ctagLen) === $this->ctag) {
                        $token = [
                            self::TYPE  => $this->tagType,
                            self::NAME  => trim($this->buffer),
                            self::OTAG  => $this->otag,
                            self::CTAG  => $this->ctag,
                            self::LINE  => $this->line,
                            self::INDEX => ($this->tagType === self::T_END_SECTION)
                                ? $this->seenTag - $this->otagLen
                                : $i + $this->ctagLen,
                        ];

                        if ($this->tagType === self::T_UNESCAPED) {
                            // Clean up `{{{ tripleStache }}}` style tokens.
                            if ($this->ctag === '}}') {
                                if (($i + 2 < $len) && $text[$i + 2] === '}') {
                                    $i++;
                                } else {
                                    $msg = sprintf(
                                        'Mismatched tag delimiters: %s on line %d',
                                        $token[self::NAME],
                                        $token[self::LINE]
                                    );

                                    throw new SyntaxException($msg, $token);
                                }
                            } else {
                                $lastName = $token[self::NAME];
                                if (str_ends_with($lastName, '}')) {
                                    $token[self::NAME] = trim(substr($lastName, 0, -1));
                                } else {
                                    $msg = sprintf(
                                        'Mismatched tag delimiters: %s on line %d',
                                        $token[self::NAME],
                                        $token[self::LINE]
                                    );

                                    throw new SyntaxException($msg, $token);
                                }
                            }
                        }

                        $this->buffer = '';
                        $i += $this->ctagLen - 1;
                        $this->state = self::IN_TEXT;
                        $this->tokens[] = $token;
                    } else {
                        $this->buffer .= $char;
                    }
                    break;
            }
        }

        if ($this->state !== self::IN_TEXT) {
            $this->throwUnclosedTagException();
        }

        $this->flushBuffer();

        return $this->tokens;
    }

    /**
     * Helper function to reset tokenizer internal state.
     */
    private function reset(): void
    {
        $this->state    = self::IN_TEXT;
        $this->tagType  = null;
        $this->buffer   = '';
        $this->tokens   = [];
        $this->seenTag  = false;
        $this->line     = 0;

        $this->otag     = '{{';
        $this->otagChar = '{';
        $this->otagLen  = 2;

        $this->ctag     = '}}';
        $this->ctagChar = '}';
        $this->ctagLen  = 2;
    }

    /**
     * Flush the current buffer to a token.
     */
    private function flushBuffer(): void
    {
        if (strlen($this->buffer) > 0) {
            $this->tokens[] = [
                self::TYPE  => self::T_TEXT,
                self::LINE  => $this->line,
                self::VALUE => $this->buffer,
            ];
            $this->buffer   = '';
        }
    }

    /**
     * Change the current Mustache delimiters. Set new `otag` and `ctag` values.
     *
     * @throws SyntaxException when delimiter string is invalid
     *
     * @param string $text  Mustache template source
     * @param int    $index Current tokenizer index
     *
     * @return int New index value
     */
    private function changeDelimiters(string $text, int $index): int
    {
        $startIndex = strpos($text, '=', $index) + 1;
        $close      = '=' . $this->ctag;
        $closeIndex = strpos($text, $close, $index);

        if ($closeIndex === false) {
            $this->throwUnclosedTagException();
        }

        $token = [
            self::TYPE => self::T_DELIM_CHANGE,
            self::LINE => $this->line,
        ];

        try {
            $this->setDelimiters(trim(substr($text, $startIndex, $closeIndex - $startIndex)));
        } catch (InvalidArgumentException $e) {
            throw new SyntaxException($e->getMessage(), $token);
        }

        $this->tokens[] = $token;

        return $closeIndex + strlen($close) - 1;
    }

    /**
     * Set the current Mustache `otag` and `ctag` delimiters.
     *
     * @throws InvalidArgumentException when delimiter string is invalid
     *
     * @param string $delimiters
     */
    private function setDelimiters(string $delimiters): void
    {
        if (!preg_match('/^\s*(\S+)\s+(\S+)\s*$/', $delimiters, $matches)) {
            throw new InvalidArgumentException(sprintf('Invalid delimiters: %s', $delimiters));
        }

        [, $otag, $ctag] = $matches;

        $this->otag     = $otag;
        $this->otagChar = $otag[0];
        $this->otagLen  = strlen($otag);

        $this->ctag     = $ctag;
        $this->ctagChar = $ctag[0];
        $this->ctagLen  = strlen($ctag);
    }

    /**
     * Add pragma token.
     *
     * Pragmas are hoisted to the front of the template, so all pragma tokens
     * will appear at the front of the token list.
     *
     * @param string $text
     * @param int    $index
     *
     * @return int New index value
     */
    private function addPragma(string $text, int $index): int
    {
        $end    = strpos($text, $this->ctag, $index);
        if ($end === false) {
            $this->throwUnclosedTagException();
        }

        $pragma = trim(substr($text, $index + 2, $end - $index - 2));

        // Pragmas are hoisted to the front of the template.
        array_unshift($this->tokens, [
            self::TYPE => self::T_PRAGMA,
            self::NAME => $pragma,
            self::LINE => 0,
        ]);

        return $end + $this->ctagLen - 1;
    }


    private function throwUnclosedTagException(): void
    {
        $name = trim($this->buffer);
        if ($name !== '') {
            $msg = sprintf('Unclosed tag: %s on line %d', $name, $this->line);
        } else {
            $msg = sprintf('Unclosed tag on line %d', $this->line);
        }

        throw new SyntaxException($msg, [
            self::TYPE  => $this->tagType,
            self::NAME  => $name,
            self::OTAG  => $this->otag,
            self::CTAG  => $this->ctag,
            self::LINE  => $this->line,
            self::INDEX => $this->seenTag - $this->otagLen,
        ]);
    }

    /**
     * Get the human-readable name for a tag type.
     *
     * @param string $tagType One of the tokenizer T_* constants
     *
     * @return string
     */
    public static function getTagName(string $tagType): string
    {
        return self::$tagNames[$tagType] ?? 'unknown';
    }
}
