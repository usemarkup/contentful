<?php

namespace Markup\Contentful\Tests\Decorator;

use Markup\Contentful\AssetInterface;
use Markup\Contentful\Decorator\AssetDecoratorInterface;
use Markup\Contentful\Decorator\CompositeAssetDecorator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CompositeAssetDecoratorTest extends MockeryTestCase
{
    /**
     * @var CompositeAssetDecorator
     */
    private $composite;

    protected function setUp()
    {
        $this->composite = new CompositeAssetDecorator();
    }

    public function testIsDecorator()
    {
        $this->assertInstanceOf(AssetDecoratorInterface::class, $this->composite);
    }

    public function testCompositeDoesNullDecorationByDefault()
    {
        $asset = $this->getMockAsset();
        $id = 42;
        $asset
            ->shouldReceive('getId')
            ->andReturn($id);
        $this->assertEquals($id, $this->composite->decorate($asset)->getId());
    }

    public function testCompositeWithTwoDecoratorsDecoratesInLifoOrder()
    {
        $initialAsset = $this->getMockAsset();
        $asset1 = $this->getMockAsset();
        $asset2 = $this->getMockAsset();
        $decorator1 = $this->getMockDecorator();
        $decorator2 = $this->getMockDecorator();
        $decorator1
            ->shouldReceive('decorate')
            ->with($initialAsset)
            ->andReturn($asset2);
        $decorator2
            ->shouldReceive('decorate')
            ->with($asset2)
            ->andReturn($asset1);
        $this->composite->addDecorator($decorator1);
        $this->composite->addDecorator($decorator2);
        $this->assertSame($asset1, $this->composite->decorate($initialAsset));
    }

    private function getMockAsset()
    {
        return m::mock(AssetInterface::class)->shouldIgnoreMissing();
    }

    private function getMockDecorator()
    {
        return m::mock(AssetDecoratorInterface::class)->shouldIgnoreMissing();
    }
}
