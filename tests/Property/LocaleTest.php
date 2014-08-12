<?php

namespace Markup\Contentful\Tests\Property;

use Markup\Contentful\Property\Locale;

class LocaleTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->localeProp = new Locale();
    }

    public function testIsProperty()
    {
        $this->assertInstanceOf('Markup\Contentful\PropertyInterface', $this->localeProp);
    }

    public function testGetKey()
    {
        $this->assertEquals('locale', $this->localeProp->getKey());
    }

    public function testToString()
    {
        $this->assertEquals('locale', strval($this->localeProp));
    }
}
