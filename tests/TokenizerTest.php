<?php

namespace Mustache\Tests;

use Mustache\Exception\SyntaxException;
use Mustache\Tokenizer;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class TokenizerTest extends TestCase
{
    /**
     * @dataProvider getTokens
     */
    public function testScan($text, $delimiters, $expected)
    {
        $tokenizer = new Tokenizer();
        $this->assertSame($expected, $tokenizer->scan($text, $delimiters));
    }

    public function testUnevenBracesThrowExceptions()
    {
        $this->expectException(SyntaxException::class);
        $tokenizer = new Tokenizer();

        $text = '{{{ name }}';
        $tokenizer->scan($text, null);
    }

    public function testUnevenBracesWithCustomDelimiterThrowExceptions()
    {
        $this->expectException(SyntaxException::class);
        $tokenizer = new Tokenizer();

        $text = '<%{ name %>';
        $tokenizer->scan($text, '<% %>');
    }

    public static function getTokens(): array
    {
        return [
            [
                'text',
                null,
                [
                    [
                        Tokenizer::TYPE  => Tokenizer::T_TEXT,
                        Tokenizer::LINE  => 0,
                        Tokenizer::VALUE => 'text',
                    ],
                ],
            ],

            [
                'text',
                '<<< >>>',
                [
                    [
                        Tokenizer::TYPE  => Tokenizer::T_TEXT,
                        Tokenizer::LINE  => 0,
                        Tokenizer::VALUE => 'text',
                    ],
                ],
            ],

            [
                '{{ name }}',
                null,
                [
                    [
                        Tokenizer::TYPE  => Tokenizer::T_ESCAPED,
                        Tokenizer::NAME  => 'name',
                        Tokenizer::OTAG  => '{{',
                        Tokenizer::CTAG  => '}}',
                        Tokenizer::LINE  => 0,
                        Tokenizer::INDEX => 10,
                    ],
                ],
            ],

            [
                '{{ name }}',
                '<<< >>>',
                [
                    [
                        Tokenizer::TYPE  => Tokenizer::T_TEXT,
                        Tokenizer::LINE  => 0,
                        Tokenizer::VALUE => '{{ name }}',
                    ],
                ],
            ],

            [
                '<<< name >>>',
                '<<< >>>',
                [
                    [
                        Tokenizer::TYPE  => Tokenizer::T_ESCAPED,
                        Tokenizer::NAME  => 'name',
                        Tokenizer::OTAG  => '<<<',
                        Tokenizer::CTAG  => '>>>',
                        Tokenizer::LINE  => 0,
                        Tokenizer::INDEX => 12,
                    ],
                ],
            ],

            [
                "{{{ a }}}\n{{# b }}  \n{{= | | =}}| c ||/ b |\n|{ d }|",
                null,
                [
                    [
                        Tokenizer::TYPE  => Tokenizer::T_UNESCAPED,
                        Tokenizer::NAME  => 'a',
                        Tokenizer::OTAG  => '{{',
                        Tokenizer::CTAG  => '}}',
                        Tokenizer::LINE  => 0,
                        Tokenizer::INDEX => 8,
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_TEXT,
                        Tokenizer::LINE  => 0,
                        Tokenizer::VALUE => "\n",
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_SECTION,
                        Tokenizer::NAME  => 'b',
                        Tokenizer::OTAG  => '{{',
                        Tokenizer::CTAG  => '}}',
                        Tokenizer::LINE  => 1,
                        Tokenizer::INDEX => 18,
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_TEXT,
                        Tokenizer::LINE  => 1,
                        Tokenizer::VALUE => "  \n",
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_DELIM_CHANGE,
                        Tokenizer::LINE  => 2,
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_ESCAPED,
                        Tokenizer::NAME  => 'c',
                        Tokenizer::OTAG  => '|',
                        Tokenizer::CTAG  => '|',
                        Tokenizer::LINE  => 2,
                        Tokenizer::INDEX => 37,
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_END_SECTION,
                        Tokenizer::NAME  => 'b',
                        Tokenizer::OTAG  => '|',
                        Tokenizer::CTAG  => '|',
                        Tokenizer::LINE  => 2,
                        Tokenizer::INDEX => 37,
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_TEXT,
                        Tokenizer::LINE  => 2,
                        Tokenizer::VALUE => "\n",
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_UNESCAPED,
                        Tokenizer::NAME  => 'd',
                        Tokenizer::OTAG  => '|',
                        Tokenizer::CTAG  => '|',
                        Tokenizer::LINE  => 3,
                        Tokenizer::INDEX => 51,
                    ],

                ],
            ],

            // See https://github.com/bobthecow/mustache.php/issues/183
            [
                '{{# a }}0{{/ a }}',
                null,
                [
                    [
                        Tokenizer::TYPE  => Tokenizer::T_SECTION,
                        Tokenizer::NAME  => 'a',
                        Tokenizer::OTAG  => '{{',
                        Tokenizer::CTAG  => '}}',
                        Tokenizer::LINE  => 0,
                        Tokenizer::INDEX => 8,
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_TEXT,
                        Tokenizer::LINE  => 0,
                        Tokenizer::VALUE => '0',
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_END_SECTION,
                        Tokenizer::NAME  => 'a',
                        Tokenizer::OTAG  => '{{',
                        Tokenizer::CTAG  => '}}',
                        Tokenizer::LINE  => 0,
                        Tokenizer::INDEX => 9,
                    ],
                ],
            ],

            // custom delimiters don't swallow the next character, even if it is a }, }}}, or the same delimiter
            [
                '<% a %>} <% b %>%> <% c %>}}}',
                '<% %>',
                [
                    [
                        Tokenizer::TYPE  => Tokenizer::T_ESCAPED,
                        Tokenizer::NAME  => 'a',
                        Tokenizer::OTAG  => '<%',
                        Tokenizer::CTAG  => '%>',
                        Tokenizer::LINE  => 0,
                        Tokenizer::INDEX => 7,
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_TEXT,
                        Tokenizer::LINE  => 0,
                        Tokenizer::VALUE => '} ',
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_ESCAPED,
                        Tokenizer::NAME  => 'b',
                        Tokenizer::OTAG  => '<%',
                        Tokenizer::CTAG  => '%>',
                        Tokenizer::LINE  => 0,
                        Tokenizer::INDEX => 16,
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_TEXT,
                        Tokenizer::LINE  => 0,
                        Tokenizer::VALUE => '%> ',
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_ESCAPED,
                        Tokenizer::NAME  => 'c',
                        Tokenizer::OTAG  => '<%',
                        Tokenizer::CTAG  => '%>',
                        Tokenizer::LINE  => 0,
                        Tokenizer::INDEX => 26,
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_TEXT,
                        Tokenizer::LINE  => 0,
                        Tokenizer::VALUE => '}}}',
                    ],
                ],
            ],

            // unescaped custom delimiters are properly parsed
            [
                '<%{ a }%>',
                '<% %>',
                [
                    [
                        Tokenizer::TYPE  => Tokenizer::T_UNESCAPED,
                        Tokenizer::NAME  => 'a',
                        Tokenizer::OTAG  => '<%',
                        Tokenizer::CTAG  => '%>',
                        Tokenizer::LINE  => 0,
                        Tokenizer::INDEX => 9,
                    ],
                ],
            ],

            // Ensure that $arg token is not picked up during tokenization
            [
                '{{$arg}}default{{/arg}}',
                null,
                [
                    [
                        Tokenizer::TYPE  => Tokenizer::T_BLOCK_VAR,
                        Tokenizer::NAME  => 'arg',
                        Tokenizer::OTAG  => '{{',
                        Tokenizer::CTAG  => '}}',
                        Tokenizer::LINE  => 0,
                        Tokenizer::INDEX => 8,
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_TEXT,
                        Tokenizer::LINE  => 0,
                        Tokenizer::VALUE => 'default',
                    ],
                    [
                        Tokenizer::TYPE  => Tokenizer::T_END_SECTION,
                        Tokenizer::NAME  => 'arg',
                        Tokenizer::OTAG  => '{{',
                        Tokenizer::CTAG  => '}}',
                        Tokenizer::LINE  => 0,
                        Tokenizer::INDEX => 15,
                    ],
                ],
            ],

            // Delimiters are trimmed
            [
                '<% name %>',
                ' <% %> ',
                [
                    [
                        Tokenizer::TYPE  => Tokenizer::T_ESCAPED,
                        Tokenizer::NAME  => 'name',
                        Tokenizer::OTAG  => '<%',
                        Tokenizer::CTAG  => '%>',
                        Tokenizer::LINE  => 0,
                        Tokenizer::INDEX => 10,
                    ],
                ],
            ],

            // An empty string makes delimiters fall back to default
            [
                '{{ name }}',
                '',
                [
                    [
                        Tokenizer::TYPE  => Tokenizer::T_ESCAPED,
                        Tokenizer::NAME  => 'name',
                        Tokenizer::OTAG  => '{{',
                        Tokenizer::CTAG  => '}}',
                        Tokenizer::LINE  => 0,
                        Tokenizer::INDEX => 10,
                    ],
                ],
            ],

            // A bad delimiter type makes delimiters fall back to default
            [
                '{{ name }}',
                42,
                [
                    [
                        Tokenizer::TYPE  => Tokenizer::T_ESCAPED,
                        Tokenizer::NAME  => 'name',
                        Tokenizer::OTAG  => '{{',
                        Tokenizer::CTAG  => '}}',
                        Tokenizer::LINE  => 0,
                        Tokenizer::INDEX => 10,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getUnclosedTags
     */
    public function testUnclosedTagsThrowExceptions($text)
    {
        $this->expectException(SyntaxException::class);
        $tokenizer = new Tokenizer();
        $tokenizer->scan($text, null);
    }

    public static function getUnclosedTags(): array
    {
        return [
            ['{{ name'],
            ['{{ name }'],
            ['{{{ name'],
            ['{{{ name }'],
            ['{{& name'],
            ['{{& name }'],
            ['{{# name'],
            ['{{# name }'],
            ['{{^ name'],
            ['{{^ name }'],
            ['{{/ name'],
            ['{{/ name }'],
            ['{{> name'],
            ['{{< name'],
            ['{{> name }'],
            ['{{< name }'],
            ['{{$ name'],
            ['{{$ name }'],
            ['{{= <% %>'],
            ['{{= <% %>='],
            ['{{= <% %>=}'],
            ['{{% name'],
            ['{{% name }'],
        ];
    }
}
