<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Filter\LocaleFilter;
use Markup\Contentful\Filter\PropertyFilter;
use Markup\Contentful\FilterInterface;
use PHPUnit\Framework\TestCase;

class LocaleFilterTest extends TestCase
{
    /**
     * @var string
     */
    private $localeString;

    /**
     * @var LocaleFilter
     */
    private $filter;

    protected function setUp()
    {
        $this->localeString = 'de_DE';
        $this->filter = new LocaleFilter($this->localeString);
    }

    public function testIsFilter()
    {
        $this->assertInstanceOf(FilterInterface::class, $this->filter);
    }

    public function testIsPropertyFilter()
    {
        $this->assertInstanceOf(PropertyFilter::class, $this->filter);
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
