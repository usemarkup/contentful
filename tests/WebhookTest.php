<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\Webhook;
use Mockery as m;

class WebhookTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->url = 'http://domain.com/hook';
        $this->metadata = m::mock('Markup\Contentful\MetadataInterface');
        $this->hook = new Webhook($this->url, $this->metadata);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsWebhook()
    {
        $this->assertInstanceOf('Markup\Contentful\WebhookInterface', $this->hook);
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
