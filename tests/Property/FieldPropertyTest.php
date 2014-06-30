<?php

namespace Markup\Contentful\Tests\Property;

use Markup\Contentful\Property\FieldProperty;

class FieldPropertyTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->propertyName = 'likes';
        $this->property = new FieldProperty($this->propertyName);
    }

    public function testIsProperty()
    {
        $this->assertInstanceOf('Markup\Contentful\PropertyInterface', $this->property);
    }

    public function testGetKey()
    {
        $this->assertEquals(sprintf('fields.%s', $this->propertyName), $this->property->getKey());
    }

    public function testToString()
    {
        $this->assertEquals(sprintf('fields.%s', $this->propertyName), strval($this->property));
    }
}
