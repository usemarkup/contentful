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
     * @var string
     */
    private $resourceType;

    /**
     * @var string
     */
    private $api;

    /**
     * @var int
     */
    private $responseCount;

    /**
     * @var bool
     */
    private $wasError;

    public function __construct(
        string $description,
        ?float $durationInSeconds,
        ?\DateTimeInterface $startTime,
        ?\DateTimeInterface $stopTime,
        string $resourceType,
        string $api,
        ?int $responseCount,
        bool $wasError = false
    ) {
        $this->description = $description;
        $this->durationInSeconds = $durationInSeconds;
        $this->startTime = $startTime;
        $this->stopTime = $stopTime;
        $this->resourceType = $resourceType;
        $this->api = $api;
        $this->responseCount = $responseCount ?? 0;
        $this->wasError = $wasError;
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

    public function getResponseCount(): int
    {
        return $this->responseCount;
    }

    public function wasError(): bool
    {
        return $this->wasError;
    }
}
