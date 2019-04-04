<?php

namespace Markup\Contentful;

trait MetadataAccessTrait
{
    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * @return MetadataInterface
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
     * @return ContentTypeInterface|null
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
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt()
    {
        return $this->getMetadata()->getCreatedAt();
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt()
    {
        return $this->getMetadata()->getUpdatedAt();
    }

    /**
     * @return string|null
     */
    public function getLocale()
    {
        return $this->getMetadata()->getLocale();
    }
}
