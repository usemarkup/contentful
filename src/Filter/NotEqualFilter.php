<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\PropertyInterface;

class NotEqualFilter extends PropertyFilter
{
    use SimpleValueTrait, ClassBasedNameTrait;

    /**
     * @param PropertyInterface $property
     * @param mixed             $value
     */
    public function __construct(PropertyInterface $property, $value)
    {
        parent::__construct($property);
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->getProperty()->getKey() . '[ne]';
    }
} 
