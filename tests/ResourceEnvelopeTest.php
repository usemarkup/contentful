<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\ResourceEnvelope;
use Mockery as m;

class ResourceEnvelopeTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->envelope = new ResourceEnvelope();
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testSetAndAccessEntries()
    {
        $entry1 = m::mock('Markup\Contentful\EntryInterface');
        $id1 = 'id1';
        $entry1
            ->shouldReceive('getId')
            ->andReturn($id1);
        $entry2 = m::mock('Markup\Contentful\EntryInterface');
        $id2 = 'id2';
        $entry2
            ->shouldReceive('getId')
            ->andReturn($id2);
        $this->envelope->insertEntry($entry1);
        $this->envelope->insertEntry($entry2);
        $this->assertSame($entry2, $this->envelope->findEntry($id2));
        $this->assertTrue($this->envelope->hasEntry($id2));
        $this->assertNull($this->envelope->findEntry('unknown'));
        $this->assertFalse($this->envelope->hasEntry('unknown'));
        $this->assertEquals(2, $this->envelope->getEntryCount());
    }

    public function testSetAndAccessAssets()
    {
        $asset1 = m::mock('Markup\Contentful\AssetInterface');
        $id1 = 'id1';
        $asset1
            ->shouldReceive('getId')
            ->andReturn($id1);
        $asset2 = m::mock('Markup\Contentful\AssetInterface');
        $id2 = 'id2';
        $asset2
            ->shouldReceive('getId')
            ->andReturn($id2);
        $this->envelope->insertAsset($asset1);
        $this->envelope->insertAsset($asset2);
        $this->assertSame($asset2, $this->envelope->findAsset($id2));
        $this->assertTrue($this->envelope->hasAsset($id2));
        $this->assertNull($this->envelope->findAsset('unknown'));
        $this->assertFalse($this->envelope->hasAsset('unknown'));
        $this->assertEquals(2, $this->envelope->getAssetCount());
    }

    public function testSetAndAccessContentTypes()
    {
        $contentType1 = m::mock('Markup\Contentful\ContentTypeInterface');
        $id1 = 'id1';
        $contentType1
            ->shouldReceive('getId')
            ->andReturn($id1);
        $contentType2 = m::mock('Markup\Contentful\ContentTypeInterface');
        $id2 = 'id2';
        $contentType2
            ->shouldReceive('getId')
            ->andReturn($id2);
        $this->envelope->insertContentType($contentType1);
        $this->envelope->insertContentType($contentType2);
        $this->assertSame($contentType2, $this->envelope->findContentType($id2));
        $this->assertTrue($this->envelope->hasContentType($id2));
        $this->assertNull($this->envelope->findContentType('unknown'));
        $this->assertFalse($this->envelope->hasContentType('unknown'));
        $this->assertEquals(2, $this->envelope->getContentTypeCount());
    }
}
