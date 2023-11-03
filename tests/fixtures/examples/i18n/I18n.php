<?php

class I18n
{
    // Variable to be interpolated
    public $name = 'Bob';

    // Add a {{#__}} lambda for i18n
    public $__ = [__CLASS__, '__trans'];

    // A *very* small i18n dictionary :)
    private static $dictionary = [
        'Hello.'                 => 'Hola.',
        'My name is {{ name }}.' => 'Me llamo {{ name }}.',
    ];

    public static function __trans($text)
    {
        return self::$dictionary[$text] ?? $text;
    }
}
