<?php

namespace Markup\Contentful\Tests\Decorator;

use Markup\Contentful\AssetInterface;
use Markup\Contentful\Decorator\AssetDecoratorInterface;
use Markup\Contentful\Decorator\NullAssetDecorator;
use PHPUnit\Framework\TestCase;

class NullAssetDecoratorTest extends TestCase
{
    protected function setUp()
    {
        $this->decorator = new NullAssetDecorator();
    }

    public function testIsDecorator()
    {
        $this->assertInstanceOf(AssetDecoratorInterface::class, $this->decorator);
    }

    public function testDecorationReturnsSameAsset()
    {
        $asset = $this->createMock(AssetInterface::class);
        $this->assertSame($asset, $this->decorator->decorate($asset));
    }
}
