<?php

namespace Mustache\Tests;

use Mustache\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class ContextTest extends TestCase
{
    public function testConstructor()
    {
        $one = new \Mustache\Context();
        $this->assertSame('', $one->find('foo'));
        $this->assertSame('', $one->find('bar'));

        $two = new \Mustache\Context(array(
            'foo' => 'FOO',
            'bar' => '<BAR>',
        ));
        $this->assertEquals('FOO', $two->find('foo'));
        $this->assertEquals('<BAR>', $two->find('bar'));

        $obj = new \StdClass();
        $obj->name = 'NAME';
        $three = new \Mustache\Context($obj);
        $this->assertSame($obj, $three->last());
        $this->assertEquals('NAME', $three->find('name'));
    }

    public function testPushPopAndLast()
    {
        $context = new \Mustache\Context();
        $this->assertFalse($context->last());

        $dummy = new TestDummy();
        $context->push($dummy);
        $this->assertSame($dummy, $context->last());
        $this->assertSame($dummy, $context->pop());
        $this->assertFalse($context->last());

        $obj = new \StdClass();
        $context->push($dummy);
        $this->assertSame($dummy, $context->last());
        $context->push($obj);
        $this->assertSame($obj, $context->last());
        $this->assertSame($obj, $context->pop());
        $this->assertSame($dummy, $context->pop());
        $this->assertFalse($context->last());
    }

    public function testFind()
    {
        $context = new \Mustache\Context();

        $dummy = new TestDummy();

        $obj = new \StdClass();
        $obj->name = 'obj';

        $arr = array(
            'a' => array('b' => array('c' => 'see')),
            'b' => 'bee',
        );

        $string = 'some arbitrary string';

        $context->push($dummy);
        $this->assertEquals('dummy', $context->find('name'));

        $context->push($obj);
        $this->assertEquals('obj', $context->find('name'));

        $context->pop();
        $this->assertEquals('dummy', $context->find('name'));

        $dummy->name = 'dummyer';
        $this->assertEquals('dummyer', $context->find('name'));

        $context->push($arr);
        $this->assertEquals('bee', $context->find('b'));
        $this->assertEquals('see', $context->findDot('a.b.c'));

        $dummy->name = 'dummy';

        $context->push($string);
        $this->assertSame($string, $context->last());
        $this->assertEquals('dummy', $context->find('name'));
        $this->assertEquals('see', $context->findDot('a.b.c'));
        $this->assertEquals('<foo>', $context->find('foo'));
        $this->assertEquals('<bar>', $context->findDot('bar'));
    }

    public function testArrayAccessFind()
    {
        $access = new TestArrayAccess(array(
            'a' => array('b' => array('c' => 'see')),
            'b' => 'bee',
        ));

        $context = new \Mustache\Context($access);
        $this->assertEquals('bee', $context->find('b'));
        $this->assertEquals('see', $context->findDot('a.b.c'));
        $this->assertEquals(null, $context->findDot('a.b.c.d'));
    }

    public function testAccessorPriority()
    {
        $context = new \Mustache\Context(new AllTheThings());

        $this->assertEquals('win', $context->find('foo'), 'method beats property');
        $this->assertEquals('win', $context->find('bar'), 'property beats ArrayAccess');
        $this->assertEquals('win', $context->find('baz'), 'ArrayAccess stands alone');
        $this->assertEquals('win', $context->find('qux'), 'ArrayAccess beats private property');
    }

    public function testAnchoredDotNotation()
    {
        $context = new \Mustache\Context();

        $a = array(
            'name'   => 'a',
            'number' => 1,
        );

        $b = array(
            'number' => 2,
            'child'  => array(
                'name' => 'baby bee',
            ),
        );

        $c = array(
            'name' => 'cee',
        );

        $context->push($a);
        $this->assertEquals('a', $context->find('name'));
        $this->assertEquals('', $context->findDot('.name'));
        $this->assertEquals('a', $context->findAnchoredDot('.name'));
        $this->assertEquals(1, $context->find('number'));
        $this->assertEquals('', $context->findDot('.number'));
        $this->assertEquals(1, $context->findAnchoredDot('.number'));

        $context->push($b);
        $this->assertEquals('a', $context->find('name'));
        $this->assertEquals(2, $context->find('number'));
        $this->assertEquals('', $context->findDot('.name'));
        $this->assertEquals('', $context->findDot('.number'));
        $this->assertEquals('', $context->findAnchoredDot('.name'));
        $this->assertEquals(2, $context->findAnchoredDot('.number'));
        $this->assertEquals('baby bee', $context->findDot('child.name'));
        $this->assertEquals('', $context->findDot('.child.name'));
        $this->assertEquals('baby bee', $context->findAnchoredDot('.child.name'));

        $context->push($c);
        $this->assertEquals('cee', $context->find('name'));
        $this->assertEquals('', $context->findDot('.name'));
        $this->assertEquals('cee', $context->findAnchoredDot('.name'));
        $this->assertEquals(2, $context->find('number'));
        $this->assertEquals('', $context->findDot('.number'));
        $this->assertEquals('', $context->findAnchoredDot('.number'));
        $this->assertEquals('baby bee', $context->findDot('child.name'));
        $this->assertEquals('', $context->findDot('.child.name'));
        $this->assertEquals('', $context->findAnchoredDot('.child.name'));
    }

    public function testAnchoredDotNotationThrowsExceptions()
    {
        $this->expectException(InvalidArgumentException::class);
        $context = new \Mustache\Context();
        $context->push(array('a' => 1));
        $context->findAnchoredDot('a');
    }
}

