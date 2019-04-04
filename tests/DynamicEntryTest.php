<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\ContentType;
use Markup\Contentful\ContentTypeField;
use Markup\Contentful\DynamicEntry;
use Markup\Contentful\Entry;
use Markup\Contentful\EntryInterface;
use Markup\Contentful\Location;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class DynamicEntryTest extends MockeryTestCase
{
    protected function setUp()
    {
        $this->entry = m::mock(Entry::class);
        $this->contentType = m::mock(ContentType::class)->shouldIgnoreMissing();
        $this->entry
            ->shouldReceive('getContentType')
            ->andReturn($this->contentType);
        $this->dynamicEntry = new DynamicEntry($this->entry, $this->contentType);
    }

    public function testIsEntry()
    {
        $this->assertInstanceOf(EntryInterface::class, $this->dynamicEntry);
    }

    public function testCoercesToDate()
    {
        $key = 'date';
        $contentTypeField = m::mock(ContentTypeField::class);
        $contentTypeField
            ->shouldReceive('getType')
            ->andReturn('Date');
        $this->contentType
            ->shouldReceive('getField')
            ->with($key)
            ->andReturn($contentTypeField);
        $this->entry
            ->shouldReceive('getField')
            ->with($key)
            ->andReturn('2014-07-27T08:00:00Z');
        $date = $this->dynamicEntry->getField($key);
        $this->assertInstanceOf(\DateTimeInterface::class, $date);
        $this->assertEquals('2014-07-27 08:00:00', $date->format('Y-m-d H:i:s'));
    }

    public function testGetLocation()
    {
        $key = 'location';
        $contentTypeField = m::mock(ContentTypeField::class);
        $contentTypeField
            ->shouldReceive('getType')
            ->andReturn('Location');
        $this->contentType
            ->shouldReceive('getField')
            ->with($key)
            ->andReturn($contentTypeField);
        $this->entry
            ->shouldReceive('getField')
            ->with($key)
            ->andReturn('23,42');
        $location = $this->dynamicEntry->getField($key);
        $this->assertInstanceOf(Location::class, $location);
        $this->assertEquals(42, $location->getLongitude());
    }

    public function testUnknownMethodCallsOnField()
    {
        $method = 'foo';
        $value = 'baz';
        $this->entry
            ->shouldReceive('getField')
            ->with($method)
            ->andReturn($value);
        $this->assertEquals($value, $this->dynamicEntry->$method());
    }

    public function testExistenceCheckChecksAgainstContentType()
    {
        $contentTypeFieldIds = ['yes', 'ja'];
        $contentTypeFields = array_map(function ($id) {
            $field = m::mock(ContentTypeField::class);
            $field
                ->shouldReceive('getId')
                ->andReturn($id);

            return $field;
        }, $contentTypeFieldIds);
        $keyedFields = [];
        foreach ($contentTypeFields as $field) {
            $keyedFields[$field->getId()] = $field;
        }
        $this->contentType
            ->shouldReceive('getFields')
            ->andReturn($keyedFields);
        $this->assertArrayHasKey('ja', $this->dynamicEntry);
        $this->assertArrayNotHasKey('nein', $this->dynamicEntry);
    }
}
