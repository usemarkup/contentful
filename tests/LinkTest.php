<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\Link;
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

    private function getMockMetadata()
    {
        return m::mock(MetadataInterface::class);
    }
}
