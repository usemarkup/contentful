<?php

namespace Markup\Contentful\Tests\Property;

use Markup\Contentful\Property\SystemProperty;
use Markup\Contentful\PropertyInterface;
use PHPUnit\Framework\TestCase;

class SystemPropertyTest extends TestCase
{
    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var SystemProperty
     */
    private $property;

    protected function setUp()
    {
        $this->propertyName = 'id';
        $this->property = new SystemProperty($this->propertyName);
    }

    public function testIsProperty()
    {
        $this->assertInstanceOf(PropertyInterface::class, $this->property);
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
