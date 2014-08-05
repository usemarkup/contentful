<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\LessThanOrEqualFilter;
use Mockery as m;

class LessThanOrEqualFilterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->property = m::mock('Markup\Contentful\PropertyInterface');
        $this->value = 3;
        $this->filter = new LessThanOrEqualFilter($this->property, $this->value);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf('Markup\Contentful\FilterInterface', $this->filter);
    }

    public function testGetName()
    {
        $this->assertEquals('less_than_or_equal', $this->filter->getName());
    }
}
