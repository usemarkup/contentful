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
     * @var \DateTimeInterface|null
     */
    private $startTime;

    /**
     * @var \DateTimeInterface|null
     */
    private $stopTime;

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
     * @var string
     */
    private $api;

    public function __construct(
        string $description,
        ?float $durationInSeconds,
        ?\DateTimeInterface $startTime,
        ?\DateTimeInterface $stopTime,
        bool $isCacheHit,
        string $type,
        string $resourceType,
        string $api
    ) {
        $this->description = $description;
        $this->durationInSeconds = $durationInSeconds;
        $this->startTime = $startTime;
        $this->stopTime = $stopTime;
        $this->isCacheHit = $isCacheHit;
        $this->type = $type;
        $this->resourceType = $resourceType;
        $this->api = $api;
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
     * @return string
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

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function getStopTime(): ?\DateTimeInterface
    {
        return $this->stopTime;
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

    /**
     * Gets the name of the API being used.
     *
     * @return string A value corresponding to one of the Contentful::*_API class constants
     */
    public function getApi()
    {
        return $this->api;
    }
}
