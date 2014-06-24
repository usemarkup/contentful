<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\Entry;
use Mockery as m;

class EntryTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->fields = [
            'foo' => 'bar',
        ];
        $this->metadata = m::mock('Markup\Contentful\MetadataInterface');
        $this->entry = new Entry($this->fields, $this->metadata);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsEntry()
    {
        $this->assertInstanceOf('Markup\Contentful\EntryInterface', $this->entry);
    }

    public function testGetFieldUsingArrayAccess()
    {
        $this->assertTrue(isset($this->entry['foo']));
        $this->assertFalse(isset($this->entry['unknown']));
        $this->assertEquals('bar', $this->entry['foo']);
        $this->assertNull($this->entry['unknown']);
    }
}
