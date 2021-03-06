<?php

namespace Markup\Contentful\Tests\Log;

use Markup\Contentful\Contentful;
use Markup\Contentful\Log\Log;
use Markup\Contentful\Log\LogInterface;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testLog()
    {
        $resourceType = LogInterface::RESOURCE_ENTRY;
        $api = Contentful::PREVIEW_API;
        $description = 'It all happened very quickly';
        $duration = 0.032;
        $startTime = new \DateTimeImmutable();
        $stopTime = new \DateTimeImmutable();
        $responseCount = 42;
        $wasError = false;
        $log = new Log(
            $description,
            $duration,
            $startTime,
            $stopTime,
            $resourceType,
            $api,
            $responseCount,
            $wasError
        );
        $this->assertInstanceOf(LogInterface::class, $log);
        $this->assertEquals($description, $log->getDescription());
        $this->assertEquals($duration, $log->getDurationInSeconds());
        $this->assertSame($startTime, $log->getStartTime());
        $this->assertSame($stopTime, $log->getStopTime());
        $this->assertEquals($resourceType, $log->getResourceType());
        $this->assertEquals($api, $log->getApi());
        $this->assertEquals($responseCount, $log->getResponseCount());
        $this->assertEquals($wasError, $log->wasError());
    }
}
