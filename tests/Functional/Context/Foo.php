<?php

namespace Mustache\Tests\Functional\Context;

final class Foo
{
    public string $name = 'Justin';
    public string $lorem = 'Lorem ipsum dolor sit amet,';
    public array $wrap;
    public array $doublewrap;
    public array $trimmer;

    public function wrapWithEm($text): string
    {
        return sprintf('<em>%s</em>', $text);
    }

    /**
     * @param string $text
     * @return string
     */
    public function wrapWithStrong(string $text): string
    {
        return sprintf('<strong>%s</strong>', $text);
    }

    public function wrapWithBoth($text): string
    {
        return self::wrapWithStrong(self::wrapWithEm($text));
    }

    public static function staticTrim($text): string
    {
        return trim($text);
    }
}
