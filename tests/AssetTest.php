<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\Asset;
use Mockery as m;

class AssetTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testIsAsset()
    {
        $refl = new \ReflectionClass('Markup\Contentful\Asset');
        $this->assertTrue($refl->implementsInterface('Markup\Contentful\AssetInterface'));
    }

    public function testCreateAssetWithNoFile()
    {
        $title = 'No File';
        $description = 'This asset has no file, and so will be in draft';
        $metadata = m::mock('Markup\Contentful\MetadataInterface');
        $asset = new Asset($title, $description, null, $metadata);
        $this->assertEquals($title, $asset->getTitle());
        $this->assertEquals($description, $asset->getDescription());
        $this->assertNull($asset->getFilename());
        $this->assertNull($asset->getContentType());
        $this->assertNull($asset->getUrl());
        $this->assertEquals([], $asset->getDetails());
        $this->assertEquals(0, $asset->getFileSizeInBytes());
    }
}
