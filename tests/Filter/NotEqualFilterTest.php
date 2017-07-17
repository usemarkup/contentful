<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\NotEqualFilter;
use Markup\Contentful\Filter\PropertyFilter;
use Markup\Contentful\FilterInterface;
use Markup\Contentful\PropertyInterface;
use Mockery as m;

class NotEqualFilterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->property = m::mock(PropertyInterface::class);
        $this->value = 'value';
        $this->filter = new NotEqualFilter($this->property, $this->value);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf(FilterInterface::class, $this->filter);
    }

    public function testIsPropertyFilter()
    {
        $this->assertInstanceOf(PropertyFilter::class, $this->filter);
    }

    public function testGetKey()
    {
        $propertyKey = 'sys.id';
        $this->property
            ->shouldReceive('getKey')
            ->andReturn($propertyKey);
        $this->assertEquals('sys.id[ne]', $this->filter->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals($this->value, $this->filter->getValue());
    }
}
