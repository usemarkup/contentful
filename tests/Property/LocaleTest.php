<?php

namespace Markup\Contentful\Tests\Property;

use Markup\Contentful\Property\Locale;
use Markup\Contentful\PropertyInterface;
use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    protected function setUp()
    {
        $this->localeProp = new Locale();
    }

    public function testIsProperty()
    {
        $this->assertInstanceOf(PropertyInterface::class, $this->localeProp);
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
