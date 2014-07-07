<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\PropertyInterface;

class ExcludeFilter extends PropertyFilter
{
    /**
     * @var array
     */
    private $values;

    /**
     * @param PropertyInterface $property
     * @param array             $values
     */
    public function __construct(PropertyInterface $property, $values)
    {
        parent::__construct($property);
        $this->values = $values;
    }

    /**
     * The key in a query string on an API request.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->getProperty()->getKey() . '[nin]';
    }

    /**
     * The value in a query string on an API request.
     *
     * @return string
     */
    public function getValue()
    {
        return implode(',', $this->values);
    }
} 
