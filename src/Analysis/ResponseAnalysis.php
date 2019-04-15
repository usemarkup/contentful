<?php
declare(strict_types=1);

namespace Markup\Contentful\Analysis;

class ResponseAnalysis implements ResponseAnalysisInterface
{
    /**
     * @var int|null
     */
    private $indicatedResponseCount;

    /**
     * @var bool
     */
    private $isError;

    public function __construct(?int $indicatedResponseCount = null, bool $isError = false)
    {
        $this->indicatedResponseCount = $indicatedResponseCount;
        $this->isError = $isError;
    }

    public function getIndicatedResponseCount(): ?int
    {
        return $this->indicatedResponseCount;
    }

    public function indicatesError(): bool
    {
        return $this->isError;
    }
}
