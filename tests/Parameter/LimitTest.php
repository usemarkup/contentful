<?php

namespace Markup\Contentful\Tests\Parameter;

use Markup\Contentful\Parameter\Limit;
use Markup\Contentful\ParameterInterface;
use PHPUnit\Framework\TestCase;

class LimitTest extends TestCase
{
    protected function setUp()
    {
        $this->count = 3;
        $this->limit = new Limit($this->count);
    }

    public function testIsParameter()
    {
        $this->assertInstanceOf(ParameterInterface::class, $this->limit);
    }

    public function testGetKey()
    {
        $this->assertEquals('limit', $this->limit->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals($this->count, $this->limit->getValue());
    }
}
