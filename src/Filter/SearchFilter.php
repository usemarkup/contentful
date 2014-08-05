<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\FilterInterface;
use Markup\Contentful\PropertyInterface;

/**
 * Filter representing a full-text search against either a specific field or all fields.
 */
class SearchFilter implements FilterInterface
{
    use SimpleValueTrait, ClassBasedNameTrait;
    
    const OPERATOR_ALL_PROPERTIES = 'query';

    /**
     * @var PropertyInterface
     */
    private $property;

    /**
     * @param string            $query
     * @param PropertyInterface $property
     */
    public function __construct($query, PropertyInterface $property = null)
    {
        $this->property = $property;
        $this->value = $query;
    }

    /**
     * The key in a query string on an API request.
     *
     * @return string
     */
    public function getKey()
    {
        if (null === $this->property) {
            return self::OPERATOR_ALL_PROPERTIES;
        }

        return $this->property->getKey() . '[match]';
    }
}
