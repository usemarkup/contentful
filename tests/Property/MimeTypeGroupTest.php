<?php

namespace Markup\Contentful\Tests\Property;

use Markup\Contentful\Property\MimeTypeGroup;
use Markup\Contentful\PropertyInterface;
use PHPUnit\Framework\TestCase;

class MimeTypeGroupTest extends TestCase
{
    /**
     * @var MimeTypeGroup
     */
    private $mimeTypeProp;

    protected function setUp()
    {
        $this->mimeTypeProp = new MimeTypeGroup();
    }

    public function testIsProperty()
    {
        $this->assertInstanceOf(PropertyInterface::class, $this->mimeTypeProp);
    }

    public function testGetKey()
    {
        $this->assertEquals('mimetype_group', $this->mimeTypeProp->getKey());
    }

    public function testToString()
    {
        $this->assertEquals('mimetype_group', strval($this->mimeTypeProp));
    }
}
