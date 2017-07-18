<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\AfterFilter;
use Markup\Contentful\Filter\DecidesCacheKeyInterface;
use Markup\Contentful\Filter\PropertyFilter;
use Markup\Contentful\FilterInterface;
use Markup\Contentful\PropertyInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AfterFilterTest extends MockeryTestCase
{
    /**
     * @var PropertyInterface|m\MockInterface
     */
    private $property;

    /**
     * @var string
     */
    private $relativeTime;

    /**
     * @var AfterFilter
     */
    private $filter;

    protected function setUp()
    {
        $this->property = m::mock(PropertyInterface::class);
        $this->relativeTime = 'now';
        $this->filter = new AfterFilter($this->property, $this->relativeTime);
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf(FilterInterface::class, $this->filter);
    }

    public function testIsPropertyFilter()
    {
        $this->assertInstanceOf(PropertyFilter::class, $this->filter);
    }

    public function testGetKeyUsesLessThan()
    {
        $propertyKey = 'created';
        $this->property
            ->shouldReceive('getKey')
            ->andReturn($propertyKey);
        $this->assertEquals('created[gt]', $this->filter->getKey());
    }

    /**
     * @group time-sensitive
     */
    public function testGetDateTimeValue()
    {
        $nowValue = \DateTime::createFromFormat('U', time())->format('Y-m-d\TH:i:s\Z');
        $this->assertEquals($nowValue, $this->filter->getValue());
    }

    public function testDecidesCacheKey()
    {
        $this->assertInstanceOf(DecidesCacheKeyInterface::class, $this->filter);
        $propertyName = 'published';
        $this->property
            ->shouldReceive('getKey')
            ->andReturn($propertyName);
        $this->assertEquals('|after|published↦now', $this->filter->getCacheKey());
    }
}
