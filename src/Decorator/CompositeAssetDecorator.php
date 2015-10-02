<?php

namespace Markup\Contentful\Decorator;

use Markup\Contentful\AssetInterface;

class CompositeAssetDecorator implements AssetDecoratorInterface
{
    /**
     * @var \SplQueue<AssetDecoratorInterface>
     */
    private $decorators;

    public function __construct()
    {
        $this->decorators = new \SplQueue();
    }

    /**
     * Decorates an asset.
     *
     * @param AssetInterface $asset
     * @return AssetInterface
     */
    public function decorate(AssetInterface $asset)
    {
        foreach ($this->decorators as $decorator) {
            /**
             * @var AssetDecoratorInterface $decorator
             */
            $asset = $decorator->decorate($asset);
        }

        return $asset;
    }

    /**
     * @param AssetDecoratorInterface $assetDecorator
     */
    public function addDecorator(AssetDecoratorInterface $assetDecorator)
    {
        $this->decorators[] = $assetDecorator;
    }
}
