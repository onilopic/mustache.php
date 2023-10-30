<?php

namespace Mustache\Tests\FiveThree\Functional;

/**
 * @group lambdas
 * @group functional
 */
class PartialLambdaIndentTest extends \PHPUnit\Framework\TestCase
{
    public function testLambdasInsidePartialsAreIndentedProperly()
    {
        $src = <<<'EOS'
<fieldset>
  {{> input }}
</fieldset>

EOS;
        $partial = <<<'EOS'
<input placeholder="{{# _t }}Enter your name{{/ _t }}">

EOS;

        $expected = <<<'EOS'
<fieldset>
  <input placeholder="ENTER YOUR NAME">
</fieldset>

EOS;

        $m = new \Mustache\Engine(array(
            'partials' => array('input' => $partial),
        ));

        $tpl = $m->loadTemplate($src);

        $data = new ClassWithLambda();
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

        $m = new \Mustache\Engine(array(
            'partials' => array('input' => $partial),
        ));

        $tpl = $m->loadTemplate($src);

        $data = new ClassWithLambda();
        $this->assertEquals($expected, $tpl->render($data));
    }
}

class ClassWithLambda
{
    public function _t()
    {
        return function ($val) {
            return strtoupper($val);
        };
    }

    public function placeholder()
    {
        return function () {
            return 'Enter your name';
        };
    }
}
