<?php

namespace Markup\Contentful\Log;

/**
 * An interface for a timer object for timing operations. Can only be started and stopped once.
 */
interface TimerInterface
{
    public function start();
    public function stop();

    /**
     * @return bool
     */
    public function isStarted();

    /**
     * @return bool
     */
    public function isStopped();

    /**
     * @return float
     */
    public function getDurationInSeconds();
}
