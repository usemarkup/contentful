<?php
declare(strict_types=1);

namespace Markup\Contentful;

use Markup\Contentful\Decorator\AssetDecoratorInterface;

/**
 * Interface for an object (usually a resource envelope) that can have an asset decorator set onto it.
 */
interface CanDecorateAssetsInterface
{
    public function setAssetDecorator(AssetDecoratorInterface $assetDecorator): void;
}
