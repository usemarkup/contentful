<?php

namespace Markup\Contentful\Log;

/**
 * An interface for an individual log for a lookup.
 */
interface LogInterface
{
    const TYPE_RESPONSE = 'response';
    const TYPE_RESOURCE = 'resource';
    const RESOURCE_CONTENT_TYPE = 'ContentType';
    const RESOURCE_ENTRY = 'Entry';
    const RESOURCE_ASSET = 'Asset';

    /**
     * The type of log. Possible values: TYPE_* interface constants.
     *
     * @return string
     */
    public function getType();

    /**
     * The resource type. Possible values: RESOURCE_* interface constants.
     */
    public function getResourceType();

    /**
     * A description of what happened, containing pertinent information.
     *
     * @return string
     */
    public function getDescription();

    /**
     * The duration of the lookup, in seconds. Returns null if none available.
     *
     * @return float|null
     */
    public function getDurationInSeconds();

    public function getStartTime(): ?\DateTimeInterface;

    public function getStopTime(): ?\DateTimeInterface;

    /**
     * Gets whether this lookup had a cache hit.
     *
     * @return bool
     */
    public function isCacheHit();

    /**
     * Gets the name of the API being used.
     *
     * @return string A value corresponding to one of the Contentful::*_API class constants
     */
    public function getApi();
}
