<?php

namespace Markup\Contentful\Decorator;

use Markup\Contentful\AssetInterface;

/**
 * Null implementation for an asset decorator.
 */
class NullAssetDecorator implements AssetDecoratorInterface
{
    /**
     * Decorates an asset.
     *
     * @param AssetInterface $asset
     * @return AssetInterface
     */
    public function decorate(AssetInterface $asset)
    {
        return $asset;
    }
}
