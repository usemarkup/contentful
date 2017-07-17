<?php

namespace Markup\Contentful\Tests\Log;

use Markup\Contentful\Log\StandardTimer;
use Markup\Contentful\Log\TimerInterface;

class StandardTimerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->timer = new StandardTimer();
    }

    public function testIsTimer()
    {
        $this->assertInstanceOf(TimerInterface::class, $this->timer);
    }

    public function testStandardCycle()
    {
        $this->assertFalse($this->timer->isStarted());
        $this->timer->start();
        $this->assertTrue($this->timer->isStarted());
        $this->assertFalse($this->timer->isStopped());
        $runningDuration = $this->timer->getDurationInSeconds();
        $this->assertInternalType('float', $runningDuration);
        $this->assertLessThan(2, $runningDuration);//seems reasonable to suppose that this will have executed in less than 2s
        $this->timer->stop();
        $this->assertTrue($this->timer->isStopped());
        $finalDuration = $this->timer->getDurationInSeconds();
        $secondFinalDuration = $this->timer->getDurationInSeconds();
        $this->assertSame($finalDuration, $secondFinalDuration);
        $this->assertInternalType('float', $finalDuration);
        $this->assertGreaterThan($runningDuration, $finalDuration);
        $this->assertLessThan(2, $finalDuration);//same applies to final duration
        $this->timer->start();
        $this->timer->stop();
        $this->assertEquals($finalDuration, $this->timer->getDurationInSeconds(), 'the timer cannot be reused');
    }
}
