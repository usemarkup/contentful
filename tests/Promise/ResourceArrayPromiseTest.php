<?php

namespace Markup\Contentful\Tests\Promise;

use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Markup\Contentful\AssetInterface;
use Markup\Contentful\EntryInterface;
use Markup\Contentful\Promise\ResourceArrayPromise;
use Markup\Contentful\ResourceArrayInterface;
use Markup\Contentful\ResourceEnvelope;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ResourceArrayPromiseTest extends MockeryTestCase
{
    /**
     * @var ResourceArrayPromise
     */
    private $resourceArray;

    protected function setUp()
    {
        $this->resourceArray = new ResourceArrayPromise(promise_for(m::mock(ResourceArrayInterface::class)));
    }

    public function testIsResourceArray()
    {
        $this->assertInstanceOf(ResourceArrayInterface::class, $this->resourceArray);
    }

    public function testIsPromise()
    {
        $this->assertInstanceOf(PromiseInterface::class, $this->resourceArray);
    }

    public function testGetEnvelope()
    {
        $envelope = m::mock(ResourceEnvelope::class);
        $inner = m::mock(ResourceArrayInterface::class)
            ->shouldReceive('getEnvelope')
            ->andReturn($envelope)
            ->getMock();
        $resourceArray = new ResourceArrayPromise(promise_for($inner));
        $this->assertSame($envelope, $resourceArray->getEnvelope());
    }

    public function testGettersForArray()
    {
        $resourceArray = new ResourceArrayPromise(promise_for([
            m::mock(EntryInterface::class),
            m::mock(AssetInterface::class),
        ]));
        $this->assertInstanceOf(ResourceEnvelope::class, $resourceArray->getEnvelope());
        $this->assertCount(2, $resourceArray);
        $this->assertInstanceOf(EntryInterface::class, $resourceArray->first());
        $this->assertInstanceOf(AssetInterface::class, $resourceArray->last());
    }
}
