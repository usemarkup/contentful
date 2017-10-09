<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\IncludeFilter;
use Markup\Contentful\Filter\PropertyFilter;
use Markup\Contentful\FilterInterface;
use Markup\Contentful\PropertyInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class IncludeFilterTest extends MockeryTestCase
{
    protected function setUp()
    {
        $this->property = m::mock(PropertyInterface::class);
        $this->values = ['foo', 'bar'];
        $this->filter = new IncludeFilter($this->property, $this->values);
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
        $this->assertEquals('sys.id[in]', $this->filter->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals('foo,bar', $this->filter->getValue());
    }
}
