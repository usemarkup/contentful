<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\IsArchivedFilter;
use Mockery as m;

class IsArchivedFilterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->value = true;
        $this->filter = new IsArchivedFilter(true);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf('Markup\Contentful\FilterInterface', $this->filter);
    }

    public function testGetKey()
    {
        $this->assertEquals('sys.archivedVersion[exists]', $this->filter->getKey());
    }

    /**
     * @dataProvider values
     */
    public function testValueIsBoolean($original, $boolean)
    {
        $filter = new IsArchivedFilter($original);
        $this->assertSame($boolean, $filter->getValue());
    }

    public function values()
    {
        return [
            ['truthy', 'true'],
            [0, 'false'],
        ];
    }
}
