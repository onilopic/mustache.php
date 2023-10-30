<?php

/**
 * @group lambdas
 * @group functional
 */
class Mustache_Test_FiveThree_Functional_ClosureQuirksTest extends PHPUnit\Framework\TestCase
{
    private $mustache;

    public function setUp(): void
    {
        $this->mustache = new Mustache_Engine();
    }

    public function testClosuresDontLikeItWhenYouTouchTheirProperties()
    {
        $tpl = $this->mustache->loadTemplate('{{ foo.bar }}');
        $this->assertEquals('', $tpl->render(array('foo' => function () {
            return 'FOO';
        })));
    }
}
