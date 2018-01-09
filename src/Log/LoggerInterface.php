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
     * @param bool $isCacheHit
     * @param TimerInterface  $timer A timer. If it is started but not stopped, it will be stopped and a reading taken. If
     * @param string $type
     * @param string $resourceType
     * @param string $api
     */
    public function log($description, $isCacheHit, TimerInterface $timer, $type, $resourceType, $api);

    /**
     * Gets the collected logs.
     *
     * @return LogInterface[]
     */
    public function getLogs();
}
