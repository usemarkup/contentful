<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\SearchFilter;
use Markup\Contentful\FilterInterface;
use Markup\Contentful\PropertyInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SearchFilterTest extends MockeryTestCase
{
    protected function setUp()
    {
        $this->query = 'look_for_me';
        $this->property = m::mock(PropertyInterface::class);
        $this->filter = new SearchFilter($this->query, $this->property);
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf(FilterInterface::class, $this->filter);
    }

    public function testGetKeyForQueryWithProperty()
    {
        $propertyKey = 'fields.description';
        $this->property
            ->shouldReceive('getKey')
            ->andReturn($propertyKey);
        $this->assertEquals('fields.description[match]', $this->filter->getKey());
    }

    public function testGetKeyForQueryWithoutProperty()
    {
        $filter = new SearchFilter($this->query);
        $this->assertEquals('query', $filter->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals($this->query, $this->filter->getValue());
    }
}
