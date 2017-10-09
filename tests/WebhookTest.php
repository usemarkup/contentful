<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\MetadataInterface;
use Markup\Contentful\Webhook;
use Markup\Contentful\WebhookInterface;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class WebhookTest extends TestCase
{
    protected function setUp()
    {
        $this->url = 'http://domain.com/hook';
        $this->metadata = m::mock(MetadataInterface::class);
        $this->hook = new Webhook($this->url, $this->metadata);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsWebhook()
    {
        $this->assertInstanceOf(WebhookInterface::class, $this->hook);
    }

    public function testGetId()
    {
        $id = '43';
        $this->metadata
            ->shouldReceive('getId')
            ->andReturn($id);
        $this->assertEquals($id, $this->hook->getId());
    }

    public function testUsesHttpBasic()
    {
        $this->assertFalse($this->hook->usesHttpBasic());
        $httpBasicHook = new Webhook($this->url, $this->metadata, 'user', 'pass');
        $this->assertTrue($httpBasicHook->usesHttpBasic());
    }
}
