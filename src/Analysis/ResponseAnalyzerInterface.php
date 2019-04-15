<?php
declare(strict_types=1);

namespace Markup\Contentful\Analysis;

interface ResponseAnalyzerInterface
{
    public function analyze(string $response): ResponseAnalysisInterface;
}
