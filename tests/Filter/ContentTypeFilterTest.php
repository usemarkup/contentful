<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\ContentTypeFilter;

class ContentTypeFilterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->value = 'page';
        $this->filter = new ContentTypeFilter($this->value);
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf('Markup\Contentful\FilterInterface', $this->filter);
    }

    public function testGetKey()
    {
        $this->assertEquals('content_type', $this->filter->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals($this->value, $this->filter->getValue());
    }
}
