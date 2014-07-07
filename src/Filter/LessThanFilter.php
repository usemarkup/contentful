<?php

namespace Markup\Contentful\Filter;

class LessThanFilter extends AbstractComparisonFilter
{
    const OPERATOR = 'lt';

    protected function getOperator()
    {
        return self::OPERATOR;
    }
} 
