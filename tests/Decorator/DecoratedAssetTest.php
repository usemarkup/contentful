<?php

namespace Markup\Contentful\Tests\Decorator;

use Mockery as m;

class DecoratedAssetTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->asset = m::mock('Markup\Contentful\AssetInterface');
        $this->decorated = new ConcreteDecoratedAsset($this->asset);
    }

    protected function tearDown()
    {
        m::close();
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
