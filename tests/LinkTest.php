<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\Link;
use Markup\Contentful\LinkInterface;
use Markup\Contentful\MetadataInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class LinkTest extends MockeryTestCase
{
    public function testGetSpaceName()
    {
        $name = 'space_name';
        $link = new Link($this->getMockMetadata(), $name);
        $this->assertEquals($name, $link->getSpaceName());
    }

    public function testIsLink()
    {
        $link = new Link($this->getMockMetadata(), 'name');
        $this->assertInstanceOf(LinkInterface::class, $link);
    }

    private function getMockMetadata()
    {
        return m::mock(MetadataInterface::class);
    }
}
