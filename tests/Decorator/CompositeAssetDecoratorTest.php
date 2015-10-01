<?php

namespace Markup\Contentful\Tests\Decorator;

use Markup\Contentful\Decorator\CompositeAssetDecorator;
use Mockery as m;

class CompositeAssetDecoratorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->composite = new CompositeAssetDecorator();
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsDecorator()
    {
        $this->assertInstanceOf('Markup\Contentful\Decorator\AssetDecoratorInterface', $this->composite);
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
        $decorator2
            ->shouldReceive('decorate')
            ->with($initialAsset)
            ->andReturn($asset2);
        $decorator1
            ->shouldReceive('decorate')
            ->with($asset2)
            ->andReturn($asset1);
        $this->composite->addDecorator($decorator1);
        $this->composite->addDecorator($decorator2);
        $this->assertSame($asset1, $this->composite->decorate($initialAsset));
    }

    private function getMockAsset()
    {
        return m::mock('Markup\Contentful\AssetInterface')->shouldIgnoreMissing();
    }

    private function getMockDecorator()
    {
        return m::mock('Markup\Contentful\Decorator\AssetDecoratorInterface')->shouldIgnoreMissing();
    }
}
