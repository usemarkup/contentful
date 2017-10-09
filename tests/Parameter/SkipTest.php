<?php

namespace Markup\Contentful\Tests\Parameter;

use Markup\Contentful\Parameter\Skip;
use Markup\Contentful\ParameterInterface;
use PHPUnit\Framework\TestCase;

class SkipTest extends TestCase
{
    protected function setUp()
    {
        $this->count = 42;
        $this->skip = new Skip($this->count);
    }

    public function testIsParameter()
    {
        $this->assertInstanceOf(ParameterInterface::class, $this->skip);
    }

    public function testGetKey()
    {
        $this->assertEquals('skip', $this->skip->getKey());
    }

    public function testGetValue()
    {
        $this->assertEquals($this->count, $this->skip->getValue());
    }
}
