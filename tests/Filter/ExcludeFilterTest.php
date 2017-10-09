<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\ExcludeFilter;
use Markup\Contentful\Filter\PropertyFilter;
use Markup\Contentful\FilterInterface;
use Markup\Contentful\PropertyInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ExcludeFilterTest extends MockeryTestCase
{
    /**
     * @var PropertyInterface|m\MockInterface
     */
    private $property;

    /**
     * @var string[]
     */
    private $values;

    /**
     * @var ExcludeFilter
     */
    private $filter;

    protected function setUp()
    {
        $this->property = m::mock(PropertyInterface::class);
        $this->values = ['foo', 'bar'];
        $this->filter = new ExcludeFilter($this->property, $this->values);
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
        $this->assertEquals('sys.id[nin]', $this->filter->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals('foo,bar', $this->filter->getValue());
    }
}
