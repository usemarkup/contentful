<?php

namespace Markup\Contentful\Tests;

use GuzzleHttp\Promise\Promise;
use function GuzzleHttp\Promise\promise_for;
use Markup\Contentful\AssetInterface;
use Markup\Contentful\ContentTypeInterface;
use Markup\Contentful\Entry;
use Markup\Contentful\EntryInterface;
use Markup\Contentful\Exception\LinkUnresolvableException;
use Markup\Contentful\Link;
use Markup\Contentful\MetadataInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class EntryTest extends MockeryTestCase
{
    /**
     * @var array
     */
    private $fields;

    /**
     * @var MetadataInterface|m\MockInterface
     */
    private $metadata;

    /**
     * @var Entry
     */
    private $entry;

    protected function setUp()
    {
        $this->fields = [
            'foo' => 'bar',
        ];
        $this->metadata = m::spy(MetadataInterface::class);
        $this->entry = new Entry($this->fields, $this->metadata);
    }

    public function testIsEntry()
    {
        $this->assertInstanceOf(EntryInterface::class, $this->entry);
    }

    public function testGetFieldsUsingArrayAccess()
    {
        $this->assertTrue(isset($this->entry['foo']));
        $this->assertFalse(isset($this->entry['unknown']));
        $this->assertEquals('bar', $this->entry['foo']);
        $this->assertNull($this->entry['unknown']);
    }

    public function testGetField()
    {
        $this->assertEquals('bar', $this->entry->getField('foo'));
        $this->assertNull($this->entry->getField('unknown'));
    }

    public function testResolveLink()
    {
        $link = m::mock(Link::class);
        $asset = m::mock(AssetInterface::class);
        $callback = function ($link) use ($asset) {
            return promise_for($asset);
        };
        $fields = [
            'asset' => $link,
        ];
        $entry = new Entry($fields, $this->metadata);
        $entry->setResolveLinkFunction($callback);
        $this->assertSame($asset, $entry['asset']);
    }

    public function testUnknownMethodFallsBackToFieldLookup()
    {
        $this->assertEquals('bar', $this->entry->foo());
        $this->assertEquals(null, $this->entry->baz());
    }

    public function testUnresolvedLinkFiltersOutFromList()
    {
        $link = m::mock(Link::class)->shouldIgnoreMissing();
        $callback = function ($link) {
            return new Promise(function () use ($link) {
                throw new LinkUnresolvableException($link);
            });
        };
        $fields = [
            'assets' => [$link],
        ];
        $entry = new Entry($fields, $this->metadata);
        $entry->setResolveLinkFunction($callback);
        $assets = $entry['assets'];
        $this->assertCount(0, $assets);
    }

    public function testUnresolvedSingleLinkEmitsNull()
    {
        $link = m::mock(Link::class)->shouldIgnoreMissing();
        $callback = function ($link) {
            return new Promise(function () use ($link) {
                throw new LinkUnresolvableException($link);
            });
        };
        $fields = [
            'asset' => $link,
        ];
        $entry = new Entry($fields, $this->metadata);
        $entry->setResolveLinkFunction($callback);
        $this->assertNull($entry['asset']);
    }

    public function testGetResolvedContentType()
    {
        $contentType = m::mock(ContentTypeInterface::class);
        $this->metadata
            ->shouldReceive('getContentType')
            ->andReturn($contentType);
        $this->assertSame($contentType, $this->entry->getContentType());
    }

    public function testGetUnresolvedContentType()
    {
        $link = m::mock(Link::class);
        $this->metadata
            ->shouldReceive('getContentType')
            ->andReturn($link);
        $contentType = m::mock(ContentTypeInterface::class);
        $callback = function ($link) use ($contentType) {
            $this->assertInstanceOf(Link::class, $link);

            return promise_for($contentType);
        };
        $this->entry->setResolveLinkFunction($callback);
        $this->assertSame($contentType, $this->entry->getContentType());
    }
}
