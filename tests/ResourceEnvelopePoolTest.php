<?php
declare(strict_types=1);

namespace Markup\Contentful\Tests;

use Markup\Contentful\ResourceEnvelope;
use Markup\Contentful\ResourceEnvelopePool;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ResourceEnvelopePoolTest extends MockeryTestCase
{
    /**
     * @var ResourceEnvelope
     */
    private $envelope;

    /**
     * @var string
     */
    private $space;

    /**
     * @var ResourceEnvelopePool
     */
    private $pool;

    protected function setUp()
    {
        $this->space = 'i_am_the_space';
        $this->envelope = m::mock(ResourceEnvelope::class);
        $this->pool = new ResourceEnvelopePool();
        $this->pool->registerEnvelopeForSpace($this->envelope, $this->space);
    }

    public function testGetEnvelope()
    {
        $this->assertSame($this->envelope, $this->pool->getEnvelopeForSpace($this->space));
    }

    public function testGetUnknownEnvelope()
    {
        $this->expectException(\RuntimeException::class);
        $this->pool->getEnvelopeForSpace('unknown');
    }
}
