<?php

namespace Markup\Contentful\Tests\Property;

use Markup\Contentful\Property\FieldProperty;
use Markup\Contentful\PropertyInterface;
use PHPUnit\Framework\TestCase;

class FieldPropertyTest extends TestCase
{
    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var FieldProperty
     */
    private $property;

    protected function setUp()
    {
        $this->propertyName = 'likes';
        $this->property = new FieldProperty($this->propertyName);
    }

    public function testIsProperty()
    {
        $this->assertInstanceOf(PropertyInterface::class, $this->property);
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
