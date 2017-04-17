<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\MimeTypeGroupFilter;

class MimeTypeGroupFilterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mimeTypeGroup = 'plaintext';
        $this->filter = new MimeTypeGroupFilter($this->mimeTypeGroup);
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf('Markup\Contentful\FilterInterface', $this->filter);
    }

    public function testIsPropertyFilter()
    {
        $this->assertInstanceOf('Markup\Contentful\Filter\PropertyFilter', $this->filter);
    }

    public function testGetKey()
    {
        $this->assertEquals('mimetype_group', $this->filter->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals($this->mimeTypeGroup, $this->filter->getValue());
    }

    public function testGetName()
    {
        $this->assertEquals('mimetype_group', $this->filter->getName());
    }

    public function testCannotCreateWithUnknownValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        new MimeTypeGroupFilter('unknown');
    }
}
