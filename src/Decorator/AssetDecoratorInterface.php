<?php

namespace Markup\Contentful\Decorator;

use Markup\Contentful\AssetInterface;

/**
 * Interface for an object that can decorate an asset when being exposed. This would typically be because your templating might be using a different CDN for serving Contentful assets.
 */
interface AssetDecoratorInterface
{
    /**
     * Decorates an asset.
     *
     * @param AssetInterface $asset
     * @return AssetInterface
     */
    public function decorate(AssetInterface $asset);
}
