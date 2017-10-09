<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\EqualFilter;
use Markup\Contentful\Filter\PropertyFilter;
use Markup\Contentful\FilterInterface;
use Markup\Contentful\PropertyInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class EqualFilterTest extends MockeryTestCase
{
    /**
     * @var PropertyInterface|m\MockInterface
     */
    private $property;

    /**
     * @var string
     */
    private $value;

    /**
     * @var EqualFilter
     */
    private $filter;

    protected function setUp()
    {
        $this->property = m::mock(PropertyInterface::class);
        $this->value = 'value';
        $this->filter = new EqualFilter($this->property, $this->value);
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
        $this->assertEquals($propertyKey, $this->filter->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals($this->value, $this->filter->getValue());
    }

    public function testGetName()
    {
        $this->assertEquals('equal', $this->filter->getName());
    }
}
