<?php

namespace Markup\Contentful;

class Metadata implements MetadataInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var SpaceInterface
     */
    private $space;

    /**
     * @var ContentTypeInterface
     */
    private $contentType;

    /**
     * @var string
     */
    private $linkType;

    /**
     * @var int
     */
    private $revision;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \Markup\Contentful\SpaceInterface $space
     */
    public function setSpace($space)
    {
        $this->space = $space;
    }

    /**
     * @return \Markup\Contentful\SpaceInterface
     */
    public function getSpace()
    {
        return $this->space;
    }

    /**
     * @param \Markup\Contentful\ContentTypeInterface $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return \Markup\Contentful\ContentTypeInterface
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param string $linkType
     * @return self
     */
    public function setLinkType($linkType)
    {
        $this->linkType = $linkType;

        return $this;
    }

    /**
     * @return string
     */
    public function getLinkType()
    {
        return $this->linkType;
    }

    /**
     * @param int $revision
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
    }

    /**
     * @return int
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param string|null $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string|null
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
