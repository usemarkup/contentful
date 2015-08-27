<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\Link;
use Mockery as m;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testGetSpaceName()
    {
        $name = 'space_name';
        $link = new Link($this->getMockMetadata(), $name);
        $this->assertEquals($name, $link->getSpaceName());
    }

    private function getMockMetadata()
    {
        return m::mock('Markup\Contentful\MetadataInterface');
    }
}
