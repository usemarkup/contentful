<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\LessThanFilter;
use Mockery as m;

class LessThanFilterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->property = m::mock('Markup\Contentful\PropertyInterface');
        $this->value = 3;
        $this->filter = new LessThanFilter($this->property, $this->value);
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

    public function testGetDateTimeValue()
    {
        $value = new \DateTime('2014-07-07 19:03:00', new \DateTimeZone('UTC'));
        $filter = new LessThanFilter($this->property, $value);
        $this->assertEquals('2014-07-07T19:03:00Z', $filter->getValue());
    }
}
