<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\NearFilter;
use Markup\Contentful\Location;
use Mockery as m;

class NearFilterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->property = m::mock('Markup\Contentful\PropertyInterface');
        $this->location = new Location(10, 20);
        $this->filter = new NearFilter($this->property, $this->location);
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
        $this->assertEquals('fields.location[near]', $this->filter->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals('10,20', $this->filter->getValue());
    }
}
