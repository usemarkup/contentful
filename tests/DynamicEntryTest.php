<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\DynamicEntry;
use Mockery as m;

class DynamicEntryTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->entry = m::mock('Markup\Contentful\Entry');
        $this->contentType = m::mock('Markup\Contentful\ContentType')->shouldIgnoreMissing();
        $this->entry
            ->shouldReceive('getContentType')
            ->andReturn($this->contentType);
        $this->dynamicEntry = new DynamicEntry($this->entry, $this->contentType);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsEntry()
    {
        $this->assertInstanceOf('Markup\Contentful\EntryInterface', $this->dynamicEntry);
    }

    public function testCoercesToDate()
    {
        $key = 'date';
        $contentTypeField = m::mock('Markup\Contentful\ContentTypeField');
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
        $this->assertInstanceOf('DateTime', $date);
        $this->assertEquals('2014-07-27 08:00:00', $date->format('Y-m-d H:i:s'));
    }

    public function testGetLocation()
    {
        $key = 'location';
        $contentTypeField = m::mock('Markup\Contentful\ContentTypeField');
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
        $this->assertInstanceOf('Markup\Contentful\Location', $location);
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
            $field = m::mock('Markup\Contentful\ContentTypeField');
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
        $this->assertTrue(isset($this->dynamicEntry['ja']));
        $this->assertFalse(isset($this->dynamicEntry['nein']));
    }
}
