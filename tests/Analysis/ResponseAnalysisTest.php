<?php
declare(strict_types=1);

namespace Markup\Contentful\Tests\Analysis;

use Markup\Contentful\Analysis\ResponseAnalysis;
use Markup\Contentful\Analysis\ResponseAnalysisInterface;
use PHPUnit\Framework\TestCase;

class ResponseAnalysisTest extends TestCase
{
    /**
     * @var int
     */
    private $responseCount;

    /**
     * @var bool
     */
    private $wasError;

    /**
     * @var ResponseAnalysis
     */
    private $analysis;

    protected function setUp()
    {
        $this->responseCount = 42;
        $this->wasError = true;
        $this->analysis = new ResponseAnalysis(
            $this->responseCount,
            $this->wasError
        );
    }

    public function testIsAnalysis()
    {
        $this->assertInstanceOf(ResponseAnalysisInterface::class, $this->analysis);
    }

    public function testGetters()
    {
        $this->assertEquals($this->responseCount, $this->analysis->getIndicatedResponseCount());
        $this->assertEquals($this->wasError, $this->analysis->indicatesError());
    }
}
