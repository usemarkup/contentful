<?php

namespace Markup\Contentful\Filter;

class LessThanOrEqualFilter extends AbstractComparisonFilter
{
    const OPERATOR = 'lte';

    protected function getOperator()
    {
        return self::OPERATOR;
    }
}
