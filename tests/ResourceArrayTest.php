<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\ResourceArray;
use Markup\Contentful\ResourceArrayInterface;
use Mockery as m;

class ResourceArrayTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testIsResourceArray()
    {
        $this->assertInstanceOf(ResourceArrayInterface::class, new ResourceArray([], 0, 0, 1));
    }

    public function testArray()
    {
        $item1 = $this->getMockEntry();
        $item2 = $this->getMockEntry();
        $items = new \ArrayIterator(['item1' => $item1, 'item2' => $item2]);
        $total = 22;
        $skip = 20;
        $limit = 10;
        $array = new ResourceArray($items, $total, $skip, $limit);
        $this->assertEquals(22, $array->getTotal());
        $this->assertEquals(20, $array->getSkip());
        $this->assertEquals(10, $array->getLimit());
        $this->assertEquals([$item1, $item2], iterator_to_array($array));
        //array access
        $this->assertSame($item1, $array[0]);
    }

    public function testIterationExcludesNull()
    {
        $item1 = $this->getMockEntry();
        $item2 = $this->getMockEntry();
        $items = ['item1' => $item1, 'item2' => $item2, 'item3' => null];
        $total = 22;
        $skip = 20;
        $limit = 10;
        $array = new ResourceArray($items, $total, $skip, $limit);
        $this->assertEquals([$item1, $item2], iterator_to_array($array));
    }

    public function testFirstWhenArrayNotEmpty()
    {
        $item1 = $this->getMockEntry();
        $item2 = $this->getMockEntry();
        $items = ['item1' => $item1, 'item2' => $item2];
        $total = 22;
        $skip = 20;
        $limit = 10;
        $array = new ResourceArray($items, $total, $skip, $limit);
        $this->assertSame($item1, $array->first());
    }

    public function testFirstWhenArrayEmptyReturnsNull()
    {
        $array = new ResourceArray([], 22, 20, 10);
        $this->assertNull($array->first());
    }

    public function testLastWhenArrayNotEmpty()
    {
        $item1 = $this->getMockEntry();
        $item2 = $this->getMockEntry();
        $items = ['item1' => $item1, 'item2' => $item2];
        $total = 22;
        $skip = 20;
        $limit = 10;
        $array = new ResourceArray($items, $total, $skip, $limit);
        $this->assertSame($item2, $array->last());
    }

    public function testLastWhenArrayEmptyReturnsNull()
    {
        $array = new ResourceArray([], 22, 20, 10);
        $this->assertNull($array->last());
    }

    private function getMockEntry()
    {
        return m::mock('Markup\Contentful\EntryInterface');
    }
}
