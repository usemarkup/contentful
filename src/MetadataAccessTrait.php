<?php

namespace Markup\Contentful;

trait MetadataAccessTrait
{
    /**
     * @var Metadata
     */
    private $metadata;

    /**
     * @return Metadata
     */
    private function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getMetadata()->getType();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getMetadata()->getId();
    }

    /**
     * @return SpaceInterface
     */
    public function getSpace()
    {
        return $this->getMetadata()->getSpace();
    }

    /**
     * @return ContentTypeInterface
     */
    public function getContentType()
    {
        return $this->getMetadata()->getContentType();
    }

    /**
     * @return string|null
     */
    public function getLinkType()
    {
        return $this->getMetadata()->getLinkType();
    }

    /**
     * @return int
     */
    public function getRevision()
    {
        return $this->getMetadata()->getRevision();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->getMetadata()->getCreatedAt();
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->getMetadata()->getUpdatedAt();
    }
} 
