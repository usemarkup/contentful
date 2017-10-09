<?php

namespace Markup\Contentful\Tests\Decorator;

use Markup\Contentful\AssetInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class DecoratedAssetTest extends MockeryTestCase
{
    protected function setUp()
    {
        $this->asset = m::mock(AssetInterface::class);
        $this->decorated = new ConcreteDecoratedAsset($this->asset);
    }

    public function testGetUrlPassesApiOptions()
    {
        $options = ['width' => 100, 'height' => 100];
        $url = 'constrained_image.jpg';
        $this->asset
            ->shouldReceive('getUrl')
            ->with($options)
            ->once()
            ->andReturn($url);
        $this->assertEquals($url, $this->decorated->getUrl($options));
    }
}
