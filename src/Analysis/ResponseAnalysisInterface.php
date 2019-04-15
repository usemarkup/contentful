<?php
declare(strict_types=1);

namespace Markup\Contentful\Analysis;

interface ResponseAnalysisInterface
{
    public function getIndicatedResponseCount(): ?int;

    public function indicatesError(): bool;
}
