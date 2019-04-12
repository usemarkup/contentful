<?php
declare(strict_types=1);

namespace Markup\Contentful\Tests\Log;

use Markup\Contentful\LinkInterface;
use Markup\Contentful\Log\LinkResolveCounter;
use Markup\Contentful\Log\LinkResolveCounterInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class LinkResolveCounterTest extends MockeryTestCase
{
    /**
     * @var LinkResolveCounter
     */
    private $counter;

    protected function setUp()
    {
        $this->counter = new LinkResolveCounter();
    }

    public function testIsCounter()
    {
        $this->assertInstanceOf(LinkResolveCounterInterface::class, $this->counter);
    }

    public function testAddLinks()
    {
        $link1 = $this->getLinkForIdAndSpaceName('jsdhfdjksh', 'space1');
        $link2 = $this->getLinkForIdAndSpaceName('kjsdhfdksj', 'space2');
        $this->assertCount(0, $this->counter);
        $this->counter->logLink($link1);
        $this->assertCount(1, $this->counter);
        $this->counter->logLink($link2);
        $this->assertCount(2, $this->counter);
        $this->counter->logLink($link2);
        $this->assertCount(2, $this->counter, 'check count in counter is still the same after a duplicate link is logged');
    }

    private function getLinkForIdAndSpaceName(string $id, string $spaceName)
    {
        return m::mock(LinkInterface::class)
            ->shouldReceive('getId')
            ->andReturn($id)
            ->getMock()
            ->shouldReceive('getSpaceName')
            ->andReturn($spaceName)
            ->getMock();
    }
}
