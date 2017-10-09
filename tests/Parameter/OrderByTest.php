<?php

namespace Markup\Contentful\Tests\Parameter;

use Markup\Contentful\Parameter\OrderBy;
use Markup\Contentful\ParameterInterface;
use Markup\Contentful\PropertyInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class OrderByTest extends MockeryTestCase
{
    protected function setUp()
    {
        $this->property = m::mock(PropertyInterface::class);
        $this->orderBy = new OrderBy($this->property);
    }

    public function testIsParameter()
    {
        $this->assertInstanceOf(ParameterInterface::class, $this->orderBy);
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
