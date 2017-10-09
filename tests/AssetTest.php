<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\Asset;
use Markup\Contentful\AssetFile;
use Markup\Contentful\AssetInterface;
use Markup\Contentful\ImageApiOptions;
use Markup\Contentful\MetadataInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AssetTest extends MockeryTestCase
{
    public function testIsAsset()
    {
        $refl = new \ReflectionClass(Asset::class);
        $this->assertTrue($refl->implementsInterface(AssetInterface::class));
    }

    public function testCreateAssetWithNoFile()
    {
        $title = 'No File';
        $description = 'This asset has no file, and so will be in draft';
        $metadata = m::mock(MetadataInterface::class);
        $asset = new Asset($title, $description, null, $metadata);
        $this->assertEquals($title, $asset->getTitle());
        $this->assertEquals($description, $asset->getDescription());
        $this->assertNull($asset->getFilename());
        $this->assertNull($asset->getContentType());
        $this->assertNull($asset->getUrl());
        $this->assertEquals([], $asset->getDetails());
        $this->assertEquals(0, $asset->getFileSizeInBytes());
    }

    public function testGetUrlUsingImageApiOptionsArray()
    {
        $assetFile = m::mock(AssetFile::class);
        $baseUrl = 'http://domain.com/image';
        $assetFile
            ->shouldReceive('getUrl')
            ->andReturn($baseUrl);
        $asset = new Asset('', '', $assetFile, m::mock(MetadataInterface::class));
        $apiOptions = [
            'width' => 300,
            'height' => 400,
            'progressive' => true,
        ];
        $expectedUrl = $baseUrl . '?fl=progressive&w=300&h=400';
        $this->assertEquals($expectedUrl, $asset->getUrl($apiOptions));
    }

    public function testGetUrlUsingImageApiOptionsObject()
    {
        $assetFile = m::mock(AssetFile::class);
        $baseUrl = 'http://domain.com/image';
        $assetFile
            ->shouldReceive('getUrl')
            ->andReturn($baseUrl);
        $asset = new Asset('', '', $assetFile, m::mock(MetadataInterface::class));
        $apiOptions = new ImageApiOptions();
        $apiOptions->setProgressive(true);
        $apiOptions->setWidth(300);
        $apiOptions->setHeight(400);
        $expectedUrl = $baseUrl . '?fl=progressive&w=300&h=400';
        $this->assertEquals($expectedUrl, $asset->getUrl($apiOptions));
    }
}
