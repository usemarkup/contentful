<?php

namespace Markup\Contentful\Log;

class NullLogger implements LoggerInterface
{
    /**
     * Gets a new timer that has already been started.
     *
     * @return TimerInterface
     */
    public function getStartedTimer()
    {
        return new NullTimer();
    }

    /**
     * Logs a lookup.
     *
     * @param string         $description A description of what this lookup was, including pertinent information such as URLs and cache keys.
     * @param TimerInterface $timer       A timer. If it is started but not stopped, it will be stopped and a reading taken. If
     * @param string         $resourceType
     * @param string         $api
     */
    public function log($description, TimerInterface $timer = null, $resourceType, $api, ?int $resourceCount, bool $wasError = false)
    {
        // do nothing
    }

    /**
     * Gets the collected logs.
     *
     * @return LogInterface[]
     */
    public function getLogs()
    {
        return [];
    }
}
