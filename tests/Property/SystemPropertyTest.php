<?php

namespace Markup\Contentful\Tests\Property;

use Markup\Contentful\Property\SystemProperty;

class SystemPropertyTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->propertyName = 'id';
        $this->property = new SystemProperty($this->propertyName);
    }

    public function testIsProperty()
    {
        $this->assertInstanceOf('Markup\Contentful\PropertyInterface', $this->property);
    }

    public function testGetKey()
    {
        $this->assertEquals(sprintf('sys.%s', $this->propertyName), $this->property->getKey());
    }

    public function testToString()
    {
        $this->assertEquals(sprintf('sys.%s', $this->propertyName), strval($this->property));
    }
}
