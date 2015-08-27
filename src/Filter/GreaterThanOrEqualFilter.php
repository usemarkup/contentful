<?php

namespace Markup\Contentful\Filter;

class GreaterThanOrEqualFilter extends AbstractComparisonFilter
{
    const OPERATOR = 'gte';

    protected function getOperator()
    {
        return self::OPERATOR;
    }
}
