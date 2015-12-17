<?php

namespace Markup\Contentful\Tests\Property;

use Markup\Contentful\Property\MimeTypeGroup;

class MimeTypeGroupTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mimeTypeProp = new MimeTypeGroup();
    }

    public function testIsProperty()
    {
        $this->assertInstanceOf('Markup\Contentful\PropertyInterface', $this->mimeTypeProp);
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
