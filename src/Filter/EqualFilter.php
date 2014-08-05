<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\PropertyInterface;

/**
 * A filter representing that a property has the given value, or contains the given value.
 */
class EqualFilter extends PropertyFilter
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
     * The key in a query string on an API request.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->getProperty()->getKey();
    }
}
