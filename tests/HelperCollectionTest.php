<?php

namespace Mustache\Tests;

use Mustache\HelperCollection;
use PHPUnit\Framework\TestCase;
use TypeError;

class HelperCollectionTest extends TestCase
{
    public function testConstructor()
    {
        $foo = [$this, 'getFoo'];
        $bar = 'BAR';

        $helpers = new HelperCollection(
            [
            'foo' => $foo,
            'bar' => $bar,
            ]
        );

        $this->assertSame($foo, $helpers->get('foo'));
        $this->assertSame($bar, $helpers->get('bar'));
    }

    public static function getFoo()
    {
        echo 'foo';
    }

    public function testAccessorsAndMutators()
    {
        $foo = [$this, 'getFoo'];
        $bar = 'BAR';

        $helpers = new HelperCollection();
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
        $foo = [$this, 'getFoo'];
        $bar = 'BAR';

        $helpers = new HelperCollection();
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
     * @param array $helpers
     * @param array $actions
     * @param null $exception
     */
    public function testHelperCollectionIsntAfraidToThrowExceptions(
        array $helpers = [],
        array $actions = [],
        $exception = null
    ) {
        if ($exception) {
            $this->expectException($exception);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $helpers = new HelperCollection($helpers);
        foreach ($actions as $method => $args) {
            call_user_func_array([$helpers, $method], $args);
        }
    }

    public function testHelperCollectionIsntAfraidToThrowError()
    {
        $this->expectException(TypeError::class);
        /** @noinspection PhpParamsInspection */
        new HelperCollection(1);
    }

    public static function getInvalidHelperArguments(): array
    {
        return [
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
