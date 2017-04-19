<?php

namespace Markup\Contentful\Tests\Promise;

use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Markup\Contentful\AssetInterface;
use Markup\Contentful\Promise\AssetPromise;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AssetPromiseTest extends MockeryTestCase
{
    /**
     * @var AssetPromise
     */
    private $asset;

    protected function setUp()
    {
        $this->asset = new AssetPromise(promise_for(m::mock(AssetInterface::class)));
    }

    public function testIsAsset()
    {
        $this->assertInstanceOf(AssetInterface::class, $this->asset);
    }

    public function testIsPromise()
    {
        $this->assertInstanceOf(PromiseInterface::class, $this->asset);
    }

    public function testGetFilename()
    {
        $inner = m::mock(AssetInterface::class);
        $filename = 'file.gif';
        $inner
            ->shouldReceive('getFilename')
            ->andReturn($filename);
        $asset = new AssetPromise(promise_for($inner));
        $this->assertEquals($filename, $asset->getFilename());
    }
}
