<?php

namespace Markup\Contentful\Tests\Log;

use Markup\Contentful\Contentful;
use Markup\Contentful\Log\Log;
use Markup\Contentful\Log\LogInterface;
use Mockery as m;

class LogTest extends \PHPUnit_Framework_TestCase
{
    public function testLog()
    {
        $type = LogInterface::TYPE_RESOURCE;
        $resourceType = LogInterface::RESOURCE_ENTRY;
        $api = Contentful::PREVIEW_API;
        $isCacheHit = true;
        $description = 'It all happened very quickly';
        $duration = 0.032;
        $log = new Log($description, $duration, $isCacheHit, $type, $resourceType, $api);
        $this->assertInstanceOf(LogInterface::class, $log);
        $this->assertEquals($type, $log->getType());
        $this->assertEquals($isCacheHit, $log->isCacheHit());
        $this->assertEquals($description, $log->getDescription());
        $this->assertEquals($duration, $log->getDurationInSeconds());
        $this->assertEquals($resourceType, $log->getResourceType());
        $this->assertEquals($api, $log->getApi());
    }
}
