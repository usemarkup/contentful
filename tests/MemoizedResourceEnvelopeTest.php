<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\AssetInterface;
use Markup\Contentful\ContentTypeInterface;
use Markup\Contentful\EntryInterface;
use Markup\Contentful\MemoizedResourceEnvelope;
use Markup\Contentful\ResourceArray;
use Markup\Contentful\ResourceEnvelopeInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class MemoizedResourceEnvelopeTest extends MockeryTestCase
{
    /**
     * @var MemoizedResourceEnvelope
     */
    private $envelope;

    protected function setUp()
    {
        $this->envelope = new MemoizedResourceEnvelope();
    }

    public function testIsResourceEnvelope()
    {
        $this->assertInstanceOf(ResourceEnvelopeInterface::class, $this->envelope);
    }

    public function testSetAndAccessEntries()
    {
        $entry1 = m::mock(EntryInterface::class);
        $id1 = 'id1';
        $entry1
            ->shouldReceive('getId')
            ->andReturn($id1);
        $entry1
            ->shouldReceive('getLocale')
            ->andReturn('fr-FR');
        $entry2 = m::mock(EntryInterface::class);
        $id2 = 'id2';
        $entry2
            ->shouldReceive('getId')
            ->andReturn($id2);
        $entry2
            ->shouldReceive('getLocale')
            ->andReturn('de-DE');
        $this->envelope->insertEntry($entry1);
        $this->envelope->insertEntry($entry2);
        $this->assertSame($entry2, $this->envelope->findEntry($id2));
        $this->assertTrue($this->envelope->hasEntry($id2));
        $this->assertNull($this->envelope->findEntry('unknown'));
        $this->assertFalse($this->envelope->hasEntry('unknown'));
        $this->assertTrue($this->envelope->hasEntry($id2, 'de-DE'));
        $this->assertFalse($this->envelope->hasEntry($id2, 'fr-FR'));
        $this->assertNull($this->envelope->findEntry($id1, 'de-DE'));
        $this->assertEquals(2, $this->envelope->getEntryCount());
    }

    public function testSetAndAccessAssets()
    {
        $asset1 = m::mock(AssetInterface::class);
        $id1 = 'id1';
        $asset1
            ->shouldReceive('getId')
            ->andReturn($id1);
        $asset1
            ->shouldReceive('getLocale')
            ->andReturn('fr-FR');
        $asset2 = m::mock(AssetInterface::class);
        $id2 = 'id2';
        $asset2
            ->shouldReceive('getId')
            ->andReturn($id2);
        $asset2
            ->shouldReceive('getLocale')
            ->andReturn('de-DE');
        $this->envelope->insertAsset($asset1);
        $this->envelope->insertAsset($asset2);
        $this->assertSame($asset2, $this->envelope->findAsset($id2));
        $this->assertTrue($this->envelope->hasAsset($id2));
        $this->assertNull($this->envelope->findAsset('unknown'));
        $this->assertFalse($this->envelope->hasAsset('unknown'));
        $this->assertFalse($this->envelope->hasAsset($id2, 'fr-FR'));
        $this->assertTrue($this->envelope->hasAsset($id2, 'de-DE'));
        $this->assertNull($this->envelope->findAsset($id1, 'de-DE'));
        $this->assertSame($asset1, $this->envelope->findAsset($id1, 'fr-FR'));
        $this->assertEquals(2, $this->envelope->getAssetCount());
    }

    public function testSetAndAccessContentTypes()
    {
        $contentType1 = m::mock(ContentTypeInterface::class);
        $id1 = 'id1';
        $contentType1
            ->shouldReceive('getId')
            ->andReturn($id1);
        $contentTypeName1 = 'name1';
        $contentType1
            ->shouldReceive('getName')
            ->andReturn($contentTypeName1);
        $contentType2 = m::mock(ContentTypeInterface::class);
        $id2 = 'id2';
        $contentType2
            ->shouldReceive('getId')
            ->andReturn($id2);
        $contentTypeName2 = 'name2';
        $contentType2
            ->shouldReceive('getName')
            ->andReturn($contentTypeName2);
        $this->envelope->insertContentType($contentType1);
        $this->envelope->insertContentType($contentType2);
        $this->assertSame($contentType2, $this->envelope->findContentType($id2));
        $this->assertTrue($this->envelope->hasContentType($id2));
        $this->assertNull($this->envelope->findContentType('unknown'));
        $this->assertFalse($this->envelope->hasContentType('unknown'));
        $this->assertEquals(2, $this->envelope->getContentTypeCount());
        $this->assertSame($contentType1, $this->envelope->findContentTypeByName($contentTypeName1));
        $this->assertNull($this->envelope->findContentTypeByName('unknown'));
    }

    public function testSetAndAccessUsingGenericInsertMethod()
    {
        $contentType1 = m::mock(ContentTypeInterface::class);
        $id1 = 'id1';
        $contentType1
            ->shouldReceive('getId')
            ->andReturn($id1);
        $contentTypeName1 = 'name1';
        $contentType1
            ->shouldReceive('getName')
            ->andReturn($contentTypeName1);
        $contentType2 = m::mock(ContentTypeInterface::class);
        $id2 = 'id2';
        $contentType2
            ->shouldReceive('getId')
            ->andReturn($id2);
        $contentTypeName2 = 'name2';
        $contentType2
            ->shouldReceive('getName')
            ->andReturn($contentTypeName2);
        $this->envelope->insert($contentType1);
        $this->envelope->insert($contentType2);
        $this->assertSame($contentType2, $this->envelope->findContentType($id2));
        $this->assertTrue($this->envelope->hasContentType($id2));
        $this->assertNull($this->envelope->findContentType('unknown'));
        $this->assertFalse($this->envelope->hasContentType('unknown'));
        $this->assertEquals(2, $this->envelope->getContentTypeCount());
        $this->assertSame($contentType1, $this->envelope->findContentTypeByName($contentTypeName1));
        $this->assertNull($this->envelope->findContentTypeByName('unknown'));
    }

    public function testSetAllContentTypesForSpace()
    {
        $space = 'ejrhgejkgh';
        $id = 'id';
        $contentType = m::mock(ContentTypeInterface::class);
        $contentType
            ->shouldReceive('getId')
            ->andReturn($id);
        $resourceArray = new ResourceArray(
            [$contentType],
            1,
            0,
            1,
            $this->envelope
        );
        $this->envelope->insertAllContentTypesForSpace($resourceArray, $space);
        $this->assertSame($contentType, $this->envelope->getAllContentTypesForSpace($space)[0]);
        $this->assertSame($contentType, $this->envelope->findContentType($id));
    }
}
