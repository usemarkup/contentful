<?php

namespace Markup\Contentful\Tests\Decorator;

use Markup\Contentful\Decorator\NullAssetDecorator;

class NullAssetDecoratorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->decorator = new NullAssetDecorator();
    }

    public function testIsDecorator()
    {
        $this->assertInstanceOf('Markup\Contentful\Decorator\AssetDecoratorInterface', $this->decorator);
    }

    public function testDecorationReturnsSameAsset()
    {
        $asset = $this->getMock('Markup\Contentful\AssetInterface');
        $this->assertSame($asset, $this->decorator->decorate($asset));
    }
}
