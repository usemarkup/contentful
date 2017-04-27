<?php

namespace Markup\Contentful\Tests\Promise;

use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Markup\Contentful\ContentTypeInterface;
use Markup\Contentful\Promise\ContentTypePromise;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ContentTypePromiseTest extends MockeryTestCase
{
    /**
     * @var ContentTypePromise
     */
    private $contentType;

    protected function setUp()
    {
        $this->contentType = new ContentTypePromise(promise_for(m::mock(ContentTypeInterface::class)));
    }

    public function testIsContentType()
    {
        $this->assertInstanceOf(ContentTypeInterface::class, $this->contentType);
    }

    public function testIsPromise()
    {
        $this->assertInstanceOf(PromiseInterface::class, $this->contentType);
    }

    public function testGetDescription()
    {
        $description = 'Mock!';
        $inner = m::mock(ContentTypeInterface::class)
            ->shouldReceive('getDescription')
            ->andReturn($description)
            ->getMock();
        $contentType = new ContentTypePromise(promise_for($inner));
        $this->assertEquals($description, $contentType->getDescription());
    }
}
