<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\IncludesEnvelope;
use Mockery as m;

class IncludesEnvelopeTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->envelope = new IncludesEnvelope();
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
        $this->assertNull($this->envelope->findEntry('unknown'));
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
        $this->assertNull($this->envelope->findAsset('unknown'));
        $this->assertEquals(2, $this->envelope->getAssetCount());
    }
}
