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
     * @param bool           $isCacheHit
     * @param TimerInterface $timer       A timer. If it is started but not stopped, it will be stopped and a reading taken. If
     * @param string         $type
     * @param string         $resourceType
     */
    public function log($description, $isCacheHit, TimerInterface $timer = null, $type, $resourceType)
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
