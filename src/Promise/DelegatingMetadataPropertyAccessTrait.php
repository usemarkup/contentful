<?php

namespace Markup\Contentful\Promise;

use Markup\Contentful\ContentTypeInterface;
use Markup\Contentful\DisallowArrayAccessMutationTrait;
use Markup\Contentful\ResourceArray;
use Markup\Contentful\ResourceInterface;
use Markup\Contentful\SpaceInterface;

trait DelegatingMetadataPropertyAccessTrait
{
    use DisallowArrayAccessMutationTrait;

    /**
     * @return ResourceInterface|ResourceArray
     */
    abstract protected function getResolved();

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->getResolved()->offsetExists($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getResolved()->offsetGet($offset);
    }

    /**
     * Gets the type of resource.
     *
     * @return string
     */
    public function getType()
    {
        return $this->getResolved()->getType();
    }

    /**
     * Gets the unique ID of the resource.
     *
     * @return string
     */
    public function getId()
    {
        return $this->getResolved()->getId();
    }

    /**
     * Gets the space this resource is associated with.
     *
     * @return SpaceInterface
     */
    public function getSpace()
    {
        return $this->getResolved()->getSpace();
    }

    /**
     * Gets the content type for an entry. (Only applicable for Entry resources.)
     *
     * @return ContentTypeInterface|null
     */
    public function getContentType()
    {
        return $this->getResolved()->getContentType();
    }

    /**
     * Gets the link type. (Only applicable for Link resources.)
     *
     * @return string|null
     */
    public function getLinkType()
    {
        return $this->getResolved()->getLinkType();
    }

    /**
     * Gets the revision number of this resource.
     *
     * @return int
     */
    public function getRevision()
    {
        return $this->getResolved()->getRevision();
    }

    /**
     * The time this resource was created.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->getResolved()->getCreatedAt();
    }

    /**
     * The time this resource was last updated.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->getResolved()->getUpdatedAt();
    }
}
