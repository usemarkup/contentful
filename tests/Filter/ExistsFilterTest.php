<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\ExistsFilter;
use Mockery as m;

class ExistsFilterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->property = m::mock('Markup\Contentful\PropertyInterface');
        $this->propertyKey = 'prop';
        $this->property
            ->shouldReceive('getKey')
            ->andReturn($this->propertyKey);
        $this->value = true;
        $this->filter = new ExistsFilter($this->property, $this->value);
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf('Markup\Contentful\FilterInterface', $this->filter);
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
            ['true', true],
            [0, false],
        ];
    }
}
