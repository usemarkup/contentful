<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\ExcludeFilter;
use Mockery as m;

class ExcludeFilterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->property = m::mock('Markup\Contentful\PropertyInterface');
        $this->values = ['foo', 'bar'];
        $this->filter = new ExcludeFilter($this->property, $this->values);
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
        $this->assertEquals('sys.id[nin]', $this->filter->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals('foo,bar', $this->filter->getValue());
    }
}
