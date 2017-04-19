<?php

namespace Markup\Contentful\Tests\Promise;

use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Markup\Contentful\Locale;
use Markup\Contentful\Promise\SpacePromise;
use Markup\Contentful\SpaceInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SpacePromiseTest extends MockeryTestCase
{
    /**
     * @var SpacePromise
     */
    private $space;

    protected function setUp()
    {
        $this->space = new SpacePromise(promise_for(SpaceInterface::class));
    }

    public function testIsSpace()
    {
        $this->assertInstanceOf(SpaceInterface::class, $this->space);
    }

    public function testIsPromise()
    {
        $this->assertInstanceOf(PromiseInterface::class, $this->space);
    }

    public function testGetName()
    {
        $name = 'outer space';
        $inner = m::mock(SpaceInterface::class)
            ->shouldReceive('getName')
            ->andReturn($name)
            ->getMock();
        $space = new SpacePromise(promise_for($inner));
        $this->assertEquals($name, $space->getName());
    }

    public function testGetLocales()
    {
        $locales = [m::mock(Locale::class)];
        $inner = m::mock(SpaceInterface::class)
            ->shouldReceive('getLocales')
            ->andReturn($locales)
            ->getMock();
        $space = new SpacePromise(promise_for($inner));
        $this->assertSame($locales, $space->getLocales());
    }
}
