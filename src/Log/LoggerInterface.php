<?php

namespace Markup\Contentful\Log;

interface LoggerInterface
{
    /**
     * Gets a new timer that has already been started.
     *
     * @return TimerInterface
     */
    public function getStartedTimer();

    /**
     * Logs a lookup.
     *
     * @param string $description A description of what this lookup was, including pertinent information such as URLs and cache keys.
     * @param TimerInterface  $timer A timer. If it is started but not stopped, it will be stopped and a reading taken. If
     * @param string $resourceType
     * @param string $api
     * @param int|null $responseCount
     * @param bool $wasError
     */
    public function log($description, TimerInterface $timer, $resourceType, $api, ?int $responseCount, bool $wasError = false);

    /**
     * Gets the collected logs.
     *
     * @return LogInterface[]
     */
    public function getLogs();
}
