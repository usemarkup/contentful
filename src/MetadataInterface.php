<?php

namespace Markup\Contentful;

/**
 * Interface for a resource's metadata (i.e. the system properties "sys" object on the APIs)
 */
interface MetadataInterface
{
    /**
     * Gets the type of resource.
     *
     * @return string
     */
    public function getType();

    /**
     * Gets the unique ID of the resource.
     *
     * @return string
     */
    public function getId();

    /**
     * Gets the space this resource is associated with.
     *
     * @return SpaceInterface
     */
    public function getSpace();

    /**
     * Gets the content type for an entry. (Only applicable for Entry resources.)
     *
     * @return ContentTypeInterface|null
     */
    public function getContentType();

    /**
     * Gets the link type. (Only applicable for Link resources.)
     *
     * @return string|null
     */
    public function getLinkType();

    /**
     * Gets the revision number of this resource.
     *
     * @return int
     */
    public function getRevision();

    /**
     * The time this resource was created.
     *
     * @return \DateTimeInterface
     */
    public function getCreatedAt();

    /**
     * The time this resource was last updated.
     *
     * @return \DateTimeInterface
     */
    public function getUpdatedAt();

    /**
     * The single locale of this resource, if there is one.
     *
     * @return string|null
     */
    public function getLocale();
}
