<?php

namespace Markup\Contentful\Log;

class NullTimer implements TimerInterface
{
    public function start()
    {
    }

    public function stop()
    {
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return false;
    }

    /**
     * @return float
     */
    public function getDurationInSeconds()
    {
        return 0.0;
    }
}
