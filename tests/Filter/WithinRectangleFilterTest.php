<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\WithinRectangleFilter;
use Markup\Contentful\FilterInterface;
use Markup\Contentful\Location;
use Markup\Contentful\PropertyInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * A filter for narrowing to locations within a bounding rectangle.
 */
class WithinRectangleFilterTest extends MockeryTestCase
{
    /**
     * @var PropertyInterface|m\MockInterface
     */
    private $property;

    /**
     * @var Location
     */
    private $bottomLeftLocation;

    /**
     * @var Location
     */
    private $topRightLocation;

    /**
     * @var WithinRectangleFilter
     */
    private $filter;

    protected function setUp()
    {
        $this->property = m::mock(PropertyInterface::class);
        $this->bottomLeftLocation = new Location(35, 55);
        $this->topRightLocation = new Location(20, 70);
        $this->filter = new WithinRectangleFilter($this->property, $this->bottomLeftLocation, $this->topRightLocation);
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf(FilterInterface::class, $this->filter);
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
        $this->assertEquals('35,55,20,70', $this->filter->getValue());
    }
}
