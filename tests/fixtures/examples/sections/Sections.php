<?php

class Sections
{
    public $start = 'It worked the first time.';

    public function middle()
    {
        return [
            ['item' => 'And it worked the second time.'],
            ['item' => 'As well as the third.'],
        ];
    }

    public $final = 'Then, surprisingly, it worked the final time.';
}
