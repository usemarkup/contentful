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
     * @var \DateTimeInterface|null
     */
    private $initialTime;

    /**
     * @var float
     */
    private $finalTimestamp;

    /**
     * \DateTimeInterface|null
     */
    private $finalTime;

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
        $this->initialTime = \DateTimeImmutable::createFromFormat('U.u', strval($this->initialTimestamp)) ?: null;
        $this->wasStarted = true;
    }

    public function stop()
    {
        if ($this->isStopped()) {
            return;
        }
        $this->finalTimestamp = $this->getCurrentTimestamp();
        $this->finalTime = \DateTimeImmutable::createFromFormat('U.u', strval($this->finalTimestamp)) ?: null;
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

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->initialTime;
    }

    public function getStopTime(): ?\DateTimeInterface
    {
        return $this->finalTime;
    }

    /**
     * @return float
     */
    private function getCurrentTimestamp()
    {
        return microtime(true);
    }
}
