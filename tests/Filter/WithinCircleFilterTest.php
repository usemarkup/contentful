<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\WithinCircleFilter;
use Markup\Contentful\Location;
use Mockery as m;

class WithinCircleFilterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->property = m::mock('Markup\Contentful\PropertyInterface');
        $this->center = new Location(15, 40);
        $this->radiusInKm = 42;
        $this->filter = new WithinCircleFilter($this->property, $this->center, $this->radiusInKm);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf('Markup\Contentful\FilterInterface', $this->filter);
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
}
