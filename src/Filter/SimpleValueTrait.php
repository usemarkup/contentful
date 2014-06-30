<?php

namespace Markup\Contentful\Filter;

trait SimpleValueTrait
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * The value in a query string on an API request.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
} 
