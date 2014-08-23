<?php

namespace Markup\Contentful\Log;

/**
 * A standard timer implementation that uses PHP microtime().
 */
class StandardTimer implements TimerInterface
{
    /**
     * @var bool
     */
    private $wasStarted;

    /**
     * @var bool
     */
    private $wasStopped;

    /**
     * @var float
     */
    private $initialTimestamp;

    /**
     * @var float
     */
    private $finalTimestamp;

    public function __construct()
    {
        $this->wasStarted = false;
        $this->wasStopped = false;
    }

    public function start()
    {
        if ($this->isStarted()) {
            return;
        }
        $this->initialTimestamp = $this->getCurrentTimestamp();
        $this->wasStarted = true;
    }

    public function stop()
    {
        if ($this->isStopped()) {
            return;
        }
        $this->finalTimestamp = $this->getCurrentTimestamp();
        $this->wasStopped = true;
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        return $this->wasStarted;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->wasStopped;
    }

    /**
     * @return float|null
     */
    public function getDurationInSeconds()
    {
        if (!$this->isStarted()) {
            return null;
        }

        return (($this->isStopped()) ? $this->finalTimestamp : $this->getCurrentTimestamp()) - $this->initialTimestamp;
    }

    /**
     * @return float
     */
    private function getCurrentTimestamp()
    {
        return microtime(true);
    }
}
