<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\LessThanFilter;
use Markup\Contentful\Filter\PropertyFilter;
use Markup\Contentful\FilterInterface;
use Markup\Contentful\PropertyInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class LessThanFilterTest extends MockeryTestCase
{
    protected function setUp()
    {
        $this->property = m::mock(PropertyInterface::class);
        $this->value = 3;
        $this->filter = new LessThanFilter($this->property, $this->value);
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf(FilterInterface::class, $this->filter);
    }

    public function testIsPropertyFilter()
    {
        $this->assertInstanceOf(PropertyFilter::class, $this->filter);
    }

    public function testGetDateTimeValue()
    {
        $value = new \DateTime('2014-07-07 19:03:00', new \DateTimeZone('UTC'));
        $filter = new LessThanFilter($this->property, $value);
        $this->assertEquals('2014-07-07T19:03:00Z', $filter->getValue());
    }
}
