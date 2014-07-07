<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\SearchFilter;
use Mockery as m;

class SearchFilterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->query = 'look_for_me';
        $this->property = m::mock('Markup\Contentful\PropertyInterface');
        $this->filter = new SearchFilter($this->query, $this->property);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf('Markup\Contentful\FilterInterface', $this->filter);
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
