<?php

namespace Mustache\Tests\FiveThree\Functional;

/**
 * @group lambdas
 * @group functional
 */
class ClosureQuirksTest extends \PHPUnit\Framework\TestCase
{
    private $mustache;

    public function setUp(): void
    {
        $this->mustache = new \Mustache\Engine();
    }

    public function testClosuresDontLikeItWhenYouTouchTheirProperties()
    {
        $tpl = $this->mustache->loadTemplate('{{ foo.bar }}');
        $this->assertEquals('', $tpl->render(array('foo' => function () {
            return 'FOO';
        })));
    }
}
