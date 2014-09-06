<?php

namespace Markup\Contentful\Tests\Parameter;

use Markup\Contentful\Parameter\OrderBy;
use Mockery as m;

class OrderByTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->property = m::mock('Markup\Contentful\PropertyInterface');
        $this->orderBy = new OrderBy($this->property);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsParameter()
    {
        $this->assertInstanceOf('Markup\Contentful\ParameterInterface', $this->orderBy);
    }

    public function testGetKey()
    {
        $this->assertEquals('order', $this->orderBy->getKey());
    }

    public function testGetValue()
    {
        $propertyKey = 'fields.birthday';
        $this->property
            ->shouldReceive('getKey')
            ->andReturn($propertyKey);
        $this->assertEquals($propertyKey, $this->orderBy->getValue());
    }

    public function testDescending()
    {
        $orderBy = new OrderBy($this->property, SORT_DESC);
        $propertyKey = 'fields.birthday';
        $this->property
            ->shouldReceive('getKey')
            ->andReturn($propertyKey);
        $this->assertEquals('-' . $propertyKey, $orderBy->getValue());
    }
}
