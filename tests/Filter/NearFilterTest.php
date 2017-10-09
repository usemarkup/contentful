<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\NearFilter;
use Markup\Contentful\FilterInterface;
use Markup\Contentful\Location;
use Markup\Contentful\PropertyInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class NearFilterTest extends MockeryTestCase
{
    protected function setUp()
    {
        $this->property = m::mock(PropertyInterface::class);
        $this->location = new Location(10, 20);
        $this->filter = new NearFilter($this->property, $this->location);
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
        $this->assertEquals('fields.location[near]', $this->filter->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals('10,20', $this->filter->getValue());
    }
}
