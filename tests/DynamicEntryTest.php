<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\DynamicEntry;
use Mockery as m;

class DynamicEntryTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->entry = m::mock('Markup\Contentful\Entry');
        $this->contentType = m::mock('Markup\Contentful\ContentType');
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

    public function testOffsetExistsDelegates()
    {
        $key = 'key';
        $this->entry
            ->shouldReceive('offsetExists')
            ->with($key)
            ->once()
            ->andReturn(true);
        $this->assertTrue(isset($this->dynamicEntry[$key]));
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
}
