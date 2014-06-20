<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\ResourceArray;
use Mockery as m;

class ResourceArrayTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testArray()
    {
        $item1 = $this->getMockEntry();
        $item2 = $this->getMockEntry();
        $items = new \ArrayIterator([$item1, $item2]);
        $total = 22;
        $skip = 20;
        $limit = 10;
        $array = new ResourceArray($items, $total, $skip, $limit);
        $this->assertEquals(22, $array->getTotal());
        $this->assertEquals(20, $array->getSkip());
        $this->assertEquals(10, $array->getLimit());
        $this->assertEquals([$item1, $item2], iterator_to_array($array));
    }

    private function getMockEntry()
    {
        return m::mock('Markup\Contentful\EntryInterface');
    }
}
