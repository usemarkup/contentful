<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\LocaleFilter;

class LocaleFilterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->localeString = 'de_DE';
        $this->filter = new LocaleFilter($this->localeString);
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
        $this->assertEquals('locale', $this->filter->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals($this->localeString, $this->filter->getValue());
    }

    public function testGetName()
    {
        $this->assertEquals('locale', $this->filter->getName());
    }
}
