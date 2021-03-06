<?php

namespace Markup\Contentful\Log;

/**
 * A standard logger implementation.
 */
class StandardLogger implements LoggerInterface
{
    /**
     * @var array<LogInterface>
     */
    private $logs;

    public function __construct()
    {
        $this->logs = [];
    }

    /**
     * Gets a new timer that has already been started.
     *
     * @return TimerInterface
     */
    public function getStartedTimer()
    {
        $timer = new StandardTimer();
        $timer->start();

        return $timer;
    }

    /**
     * Logs a lookup.
     *
     * @param string         $description A description of what this lookup was, including pertinent information such as URLs and cache keys.
     * @param TimerInterface $timer       A timer. If it is started but not stopped, it will be stopped and a reading taken. If not started, no reading.
     * @param string         $resourceType
     * @param string         $api
     * @param int|null $responseCount
     * @param bool $wasError
     */
    public function log($description, TimerInterface $timer, $resourceType, $api, ?int $responseCount, bool $wasError = false)
    {
        if ($timer->isStarted()) {
            $timer->stop();//will have no effect if already stopped
            $duration = $timer->getDurationInSeconds();
        } else {
            $duration = null;
        }
        $this->logs[] = new Log(
            $description,
            $duration,
            $timer->getStartTime(),
            $timer->getStopTime(),
            $resourceType,
            $api,
            $responseCount,
            $wasError
        );
    }

    /**
     * Gets the collected logs.
     *
     * @return LogInterface[]
     */
    public function getLogs()
    {
        return $this->logs;
    }
}
