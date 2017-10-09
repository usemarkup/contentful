<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\LessThanOrEqualFilter;
use Markup\Contentful\FilterInterface;
use Markup\Contentful\PropertyInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class LessThanOrEqualFilterTest extends MockeryTestCase
{
    protected function setUp()
    {
        $this->property = m::mock(PropertyInterface::class);
        $this->value = 3;
        $this->filter = new LessThanOrEqualFilter($this->property, $this->value);
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf(FilterInterface::class, $this->filter);
    }

    public function testGetName()
    {
        $this->assertEquals('less_than_or_equal', $this->filter->getName());
    }
}
