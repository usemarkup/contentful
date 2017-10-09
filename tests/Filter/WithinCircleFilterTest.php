<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\WithinCircleFilter;
use Markup\Contentful\FilterInterface;
use Markup\Contentful\Location;
use Markup\Contentful\PropertyInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class WithinCircleFilterTest extends MockeryTestCase
{
    protected function setUp()
    {
        $this->property = m::mock(PropertyInterface::class);
        $this->center = new Location(15, 40);
        $this->radiusInKm = 42;
        $this->filter = new WithinCircleFilter($this->property, $this->center, $this->radiusInKm);
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf(FilterInterface::class, $this->filter);
    }

    public function testGetKey()
    {
        $propertyKey = 'fields.location';
        $this->property
            ->shouldReceive('getKey')
            ->andReturn($propertyKey);
        $this->assertEquals('fields.location[within]', $this->filter->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals('15,40,42', $this->filter->getValue());
    }

    public function testGetName()
    {
        $this->assertEquals('within_circle', $this->filter->getName());
    }
}
