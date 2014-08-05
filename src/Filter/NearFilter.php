<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\FilterInterface;
use Markup\Contentful\Location;
use Markup\Contentful\PropertyInterface;

/**
 * A filter returning entries near to a location, ordered by distance.
 */
class NearFilter implements FilterInterface
{
    use ClassBasedNameTrait;

    /**
     * @var PropertyInterface
     */
    private $property;

    /**
     * @var Location
     */
    private $location;

    /**
     * @param PropertyInterface $property
     * @param Location $location
     */
    public function __construct(PropertyInterface $property, Location $location)
    {
        $this->property = $property;
        $this->location = $location;
    }

    /**
     * The key in a query string on an API request.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->property->getKey() . '[near]';
    }

    /**
     * The value in a query string on an API request.
     *
     * @return string
     */
    public function getValue()
    {
        return implode(',', [$this->location->getLatitude(), $this->location->getLongitude()]);
    }
}
