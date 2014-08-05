<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\PropertyInterface;

/**
 * A filter for when a value is found within a set of possible values.
 */
class IncludeFilter extends PropertyFilter
{
    use ClassBasedNameTrait;

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
        return $this->getProperty()->getKey() . '[in]';
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
