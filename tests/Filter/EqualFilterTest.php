<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\EqualFilter;
use Mockery as m;

class EqualFilterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->property = m::mock('Markup\Contentful\PropertyInterface');
        $this->value = 'value';
        $this->filter = new EqualFilter($this->property, $this->value);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf('Markup\Contentful\FilterInterface', $this->filter);
    }

    public function testIsPropertyFilter()
    {
        $this->assertInstanceOf('Markup\Contentful\Filter\PropertyFilter', $this->filter);
    }

    public function testGetKey()
    {
        $propertyKey = 'sys.id';
        $this->property
            ->shouldReceive('getKey')
            ->andReturn($propertyKey);
        $this->assertEquals($propertyKey, $this->filter->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals($this->value, $this->filter->getValue());
    }
}
