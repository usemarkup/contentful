<?php

namespace Markup\Contentful\Tests\Promise;

use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Markup\Contentful\EntryInterface;
use Markup\Contentful\Promise\EntryPromise;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class EntryPromiseTest extends MockeryTestCase
{
    /**
     * @var EntryPromise
     */
    private $entry;

    protected function setUp()
    {
        $this->entry = new EntryPromise(promise_for(m::mock(EntryInterface::class)));
    }

    public function testIsEntry()
    {
        $this->assertInstanceOf(EntryInterface::class, $this->entry);
    }

    public function testIsPromise()
    {
        $this->assertInstanceOf(PromiseInterface::class, $this->entry);
    }

    public function testGetField()
    {
        $inner = m::mock(EntryInterface::class);
        $inner
            ->shouldReceive('getField')
            ->with(m::type('string'))
            ->andReturnUsing(function ($fieldName) {
                return $fieldName;
            });
        $entry = new EntryPromise(promise_for($inner));
        $this->assertEquals('yayayay', $entry->getField('yayayay'));
    }
}
