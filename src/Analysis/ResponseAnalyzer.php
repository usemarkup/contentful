<?php
declare(strict_types=1);

namespace Markup\Contentful\Analysis;

class ResponseAnalyzer implements ResponseAnalyzerInterface
{
    public function analyze(string $response): ResponseAnalysisInterface
    {
        //check if we have an array
        if ($this->checkForType($response, 'Array')) {
            preg_match('/total.{3}(\d+),/', $response, $matches);

            return new ResponseAnalysis((isset($matches[1])) ? intval($matches[1]) : null);
        }
        if ($this->checkForType($response,'Error')) {
            return $this->createErrorAnalysis();
        }
        if ($this->checkForType($response, 'Entry') || $this->checkForType($response, 'Asset')) {
            return $this->createGoodAnalysisForSingleResource();
        }

        return $this->createEmptyAnalysis();
    }

    private function checkForType(string $response, string $type): bool
    {
        return (bool) preg_match(sprintf('/type.{4}%s/', preg_quote($type)), $response);
    }

    private function createGoodAnalysisForSingleResource()
    {
        return new ResponseAnalysis(1);
    }

    private function createErrorAnalysis()
    {
        return new ResponseAnalysis(null, true);
    }

    private function createEmptyAnalysis()
    {
        return new ResponseAnalysis();
    }
}
