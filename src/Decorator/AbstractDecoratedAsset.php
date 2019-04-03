<?php

namespace Markup\Contentful\Decorator;

use Markup\Contentful\AssetInterface;
use Markup\Contentful\SpaceInterface;

/**
 * Abstract class that provides decoration of an underlying asset.
 */
abstract class AbstractDecoratedAsset implements AssetInterface
{
    /**
     * @var AssetInterface
     */
    protected $decorated;

    /**
     * @param AssetInterface $asset
     */
    public function __construct(AssetInterface $asset)
    {
        $this->decorated = $asset;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->decorated->getTitle();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->decorated->getDescription();
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->decorated->getFilename();
    }

    public function getMimeType()
    {
        return $this->decorated->getMimeType();
    }

    /**
     * Gets the content type of the asset. This is a MIME type, *not* a Contentful type.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->decorated->getContentType();
    }

    /**
     * @param array|ImageApiOptions
     * @return string
     */
    public function getUrl($imageApiOptions = null)
    {
        return $this->decorated->getUrl($imageApiOptions);
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->decorated->getDetails();
    }

    /**
     * @return int
     */
    public function getFileSizeInBytes()
    {
        return $this->decorated->getFileSizeInBytes();
    }


    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->decorated->getWidth();
    }


    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->decorated->getHeight();
    }


    /**
     * @return int
     */
    public function getRatio()
    {
        return $this->decorated->getRatio();
    }

    /**
     * Gets the type of resource.
     *
     * @return string
     */
    public function getType()
    {
        return $this->decorated->getType();
    }

    /**
     * Gets the unique ID of the resource.
     *
     * @return string
     */
    public function getId()
    {
        return $this->decorated->getId();
    }

    /**
     * Gets the space this resource is associated with.
     *
     * @return SpaceInterface
     */
    public function getSpace()
    {
        return $this->decorated->getSpace();
    }

    /**
     * Gets the link type. (Only applicable for Link resources.)
     *
     * @return string|null
     */
    public function getLinkType()
    {
        return $this->decorated->getLinkType();
    }

    /**
     * Gets the revision number of this resource.
     *
     * @return int
     */
    public function getRevision()
    {
        return $this->decorated->getRevision();
    }

    /**
     * The time this resource was created.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->decorated->getCreatedAt();
    }

    /**
     * The time this resource was last updated.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->decorated->getUpdatedAt();
    }

    /**
     * Gets the locale for the asset, if there is one.
     *
     * @return null|string
     */
    public function getLocale()
    {
        return $this->decorated->getLocale();
    }
}
