<?php

namespace Mustache\Tests;

class HelperCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $foo = array($this, 'getFoo');
        $bar = 'BAR';

        $helpers = new \Mustache\HelperCollection(array(
            'foo' => $foo,
            'bar' => $bar,
        ));

        $this->assertSame($foo, $helpers->get('foo'));
        $this->assertSame($bar, $helpers->get('bar'));
    }

    public static function getFoo()
    {
        echo 'foo';
    }

    public function testAccessorsAndMutators()
    {
        $foo = array($this, 'getFoo');
        $bar = 'BAR';

        $helpers = new \Mustache\HelperCollection();
        $this->assertTrue($helpers->isEmpty());
        $this->assertFalse($helpers->has('foo'));
        $this->assertFalse($helpers->has('bar'));

        $helpers->add('foo', $foo);
        $this->assertFalse($helpers->isEmpty());
        $this->assertTrue($helpers->has('foo'));
        $this->assertFalse($helpers->has('bar'));

        $helpers->add('bar', $bar);
        $this->assertFalse($helpers->isEmpty());
        $this->assertTrue($helpers->has('foo'));
        $this->assertTrue($helpers->has('bar'));

        $helpers->remove('foo');
        $this->assertFalse($helpers->isEmpty());
        $this->assertFalse($helpers->has('foo'));
        $this->assertTrue($helpers->has('bar'));
    }

    public function testMagicMethods()
    {
        $foo = array($this, 'getFoo');
        $bar = 'BAR';

        $helpers = new \Mustache\HelperCollection();
        $this->assertTrue($helpers->isEmpty());
        $this->assertFalse($helpers->has('foo'));
        $this->assertFalse($helpers->has('bar'));
        $this->assertFalse(isset($helpers->foo));
        $this->assertFalse(isset($helpers->bar));

        $helpers->foo = $foo;
        $this->assertFalse($helpers->isEmpty());
        $this->assertTrue($helpers->has('foo'));
        $this->assertFalse($helpers->has('bar'));
        $this->assertTrue(isset($helpers->foo));
        $this->assertFalse(isset($helpers->bar));

        $helpers->bar = $bar;
        $this->assertFalse($helpers->isEmpty());
        $this->assertTrue($helpers->has('foo'));
        $this->assertTrue($helpers->has('bar'));
        $this->assertTrue(isset($helpers->foo));
        $this->assertTrue(isset($helpers->bar));

        unset($helpers->foo);
        $this->assertFalse($helpers->isEmpty());
        $this->assertFalse($helpers->has('foo'));
        $this->assertTrue($helpers->has('bar'));
        $this->assertFalse(isset($helpers->foo));
        $this->assertTrue(isset($helpers->bar));
    }

    /**
     * @dataProvider getInvalidHelperArguments
     */
    public function testHelperCollectionIsntAfraidToThrowExceptions($helpers = array(), $actions = array(), $exception = null)
    {
        if ($exception) {
            $this->expectException($exception);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $helpers = new \Mustache\HelperCollection($helpers);

        foreach ($actions as $method => $args) {
            call_user_func_array(array($helpers, $method), $args);
        }
    }

    public static function getInvalidHelperArguments(): array
    {
        return [
            [
                'not helpers',
                [],
                'InvalidArgumentException',
            ],
            [
                [],
                ['get' => ['foo']],
                'InvalidArgumentException',
            ],
            [
                ['foo' => 'FOO'],
                ['get' => ['foo']],
                null,
            ],
            [
                ['foo' => 'FOO'],
                ['get' => ['bar']],
                'InvalidArgumentException',
            ],
            [
                ['foo' => 'FOO'],
                [
                    'add' => ['bar', 'BAR'],
                    'get' => ['bar'],
                ],
                null,
            ],
            [
                ['foo' => 'FOO'],
                [
                    'get'    => ['foo'],
                    'remove' => ['foo'],
                ],
                null,
            ],
            [
                ['foo' => 'FOO'],
                [
                    'remove' => ['foo'],
                    'get'    => ['foo'],
                ],
                'InvalidArgumentException',
            ],
            [
                [],
                ['remove' => ['foo']],
                'InvalidArgumentException',
            ],
        ];
    }
}
