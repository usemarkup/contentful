<?php

namespace Markup\Contentful\Tests\Log;

use Markup\Contentful\Contentful;
use Markup\Contentful\Log\LoggerInterface;
use Markup\Contentful\Log\LogInterface;
use Markup\Contentful\Log\StandardLogger;
use Markup\Contentful\Log\TimerInterface;

class StandardLoggerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->logger = new StandardLogger();
    }

    public function testIsLogger()
    {
        $this->assertInstanceOf(LoggerInterface::class, $this->logger);
    }

    public function testLogOneItem()
    {
        $initialLogs = $this->logger->getLogs();
        $this->assertCount(0, $initialLogs);
        $timer = $this->logger->getStartedTimer();
        $this->assertInstanceOf(TimerInterface::class, $timer);
        $this->assertTrue($timer->isStarted());
        $description = 'description goes here';
        $isCacheHit = true;
        $type = LogInterface::TYPE_RESOURCE;
        $resourceType = LogInterface::RESOURCE_ASSET;
        $api = Contentful::CONTENT_DELIVERY_API;
        $this->logger->log($description, $isCacheHit, $timer, $type, $resourceType, $api);
        $finalLogs = $this->logger->getLogs();
        $this->assertCount(1, $finalLogs);
        $log = reset($finalLogs);
        $this->assertInstanceOf(LogInterface::class, $log);
        $this->assertEquals($type, $log->getType());
        $this->assertEquals($description, $log->getDescription());
        $this->assertInternalType('float', $log->getDurationInSeconds());
        $this->assertLessThan(1, $log->getDurationInSeconds());
        $this->assertEquals($api, $log->getApi());
    }
}
