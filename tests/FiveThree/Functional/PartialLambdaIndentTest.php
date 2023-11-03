<?php

namespace Mustache\Tests\FiveThree\Functional;

use Mustache\Engine;
use PHPUnit\Framework\TestCase;

/**
 * @group lambdas
 * @group functional
 */
class PartialLambdaIndentTest extends TestCase
{
    public function testLambdasInsidePartialsAreIndentedProperly()
    {
        $src = <<<'EOS'
            <fieldset>
              {{> input }}
            </fieldset>

            EOS;
        $partial = <<<'EOS'
            <input placeholder="{{# toUpper }}Enter your name{{/ toUpper }}">

            EOS;

        $expected = <<<'EOS'
            <fieldset>
              <input placeholder="ENTER YOUR NAME">
            </fieldset>

            EOS;

        $m = new Engine(
            [
            'partials' => ['input' => $partial],
            ]
        );

        $tpl = $m->loadTemplate($src);

        $data = new Context\ClassWithLambda();
        $this->assertEquals($expected, $tpl->render($data));
    }

    public function testLambdaInterpolationsInsidePartialsAreIndentedProperly()
    {
        $src = <<<'EOS'
            <fieldset>
              {{> input }}
            </fieldset>

            EOS;
        $partial = <<<'EOS'
            <input placeholder="{{ placeholder }}">

            EOS;

        $expected = <<<'EOS'
            <fieldset>
              <input placeholder="Enter your name">
            </fieldset>

            EOS;

        $m = new Engine(
            [
            'partials' => ['input' => $partial],
            ]
        );

        $tpl = $m->loadTemplate($src);

        $data = new Context\ClassWithLambda();
        $this->assertEquals($expected, $tpl->render($data));
    }
}
