<?php

namespace Markup\Contentful\Filter;

class GreaterThanFilter extends AbstractComparisonFilter
{
    const OPERATOR = 'gt';

    protected function getOperator()
    {
        return self::OPERATOR;
    }
} 
