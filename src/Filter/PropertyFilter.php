<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\FilterInterface;
use Markup\Contentful\PropertyInterface;

/**
 * A filter on a given property.
 */
abstract class PropertyFilter implements FilterInterface
{
    /**
     * @var PropertyInterface
     */
    private $property;

    /**
     * @param PropertyInterface $property
     */
    public function __construct(PropertyInterface $property)
    {
        $this->property = $property;
    }

    /**
     * @return PropertyInterface
     */
    public function getProperty()
    {
        return $this->property;
    }
}
