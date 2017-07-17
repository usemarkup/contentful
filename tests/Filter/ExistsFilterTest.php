<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\ExistsFilter;
use Markup\Contentful\FilterInterface;
use Markup\Contentful\PropertyInterface;
use Mockery as m;

class ExistsFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PropertyInterface|m\MockInterface
     */
    private $property;

    /**
     * @var bool
     */
    private $value;

    /**
     * @var ExistsFilter
     */
    private $filter;

    protected function setUp()
    {
        $this->property = m::mock(PropertyInterface::class);
        $this->propertyKey = 'prop';
        $this->property
            ->shouldReceive('getKey')
            ->andReturn($this->propertyKey);
        $this->value = true;
        $this->filter = new ExistsFilter($this->property, $this->value);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf(FilterInterface::class, $this->filter);
    }

    public function testGetKey()
    {
        $this->assertEquals('prop[exists]', $this->filter->getKey());
    }

    /**
     * @dataProvider values
     */
    public function testGetValueReturnsBoolean($original, $boolean)
    {
        $filter = new ExistsFilter($this->property, $original);
        $this->assertSame($boolean, $filter->getValue());
    }

    public function values()
    {
        return [
            ['truthiness', 'true'],
            [0, 'false'],
        ];
    }
}
