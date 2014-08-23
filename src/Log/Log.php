<?php

namespace Markup\Contentful\Log;

class Log implements LogInterface
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var float|null
     */
    private $durationInSeconds;

    /**
     * @var bool
     */
    private $isCacheHit;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $resourceType;

    /**
     * @param string $description
     * @param float|null $durationInSeconds
     * @param bool $isCacheHit
     * @param string $type
     * @param string $resourceType
     */
    public function __construct($description, $durationInSeconds, $isCacheHit, $type, $resourceType)
    {
        $this->description = $description;
        $this->durationInSeconds = $durationInSeconds;
        $this->isCacheHit = (bool) $isCacheHit;
        $this->type = $type;
        $this->resourceType = $resourceType;
    }

    /**
     * The type of log. Possible values: TYPE_* interface constants
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * A description of what happened, containing pertinent information.
     *
     * @var string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * The duration of the lookup, in seconds. Returns null if none available.
     *
     * @return float|null
     */
    public function getDurationInSeconds()
    {
        return $this->durationInSeconds;
    }

    /**
     * Gets whether this lookup had a cache hit.
     *
     * @return bool
     */
    public function isCacheHit()
    {
        return $this->isCacheHit;
    }

    /**
     * The resource type. Possible values: RESOURCE_* interface constants.
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }
}
