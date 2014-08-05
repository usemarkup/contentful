<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\FilterInterface;
use Markup\Contentful\Location;
use Markup\Contentful\PropertyInterface;

class WithinCircleFilter implements FilterInterface
{
    use ClassBasedNameTrait;

    /**
     * @var PropertyInterface
     */
    private $property;

    /**
     * @var Location
     */
    private $center;

    /**
     * @var float
     */
    private $radiusInKm;

    /**
     * @param PropertyInterface $property
     * @param Location          $center
     * @param float             $radiusInKm
     */
    public function __construct(PropertyInterface $property, Location $center, $radiusInKm)
    {
        $this->property = $property;
        $this->center = $center;
        $this->radiusInKm = $radiusInKm;
    }

    /**
     * The key in a query string on an API request.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->property->getKey() . '[within]';
    }

    /**
     * The value in a query string on an API request.
     *
     * @return string
     */
    public function getValue()
    {
        return implode(
            ',',
            [
                $this->center->getLatitude(),
                $this->center->getLongitude(),
                $this->radiusInKm,
            ]
        );
    }
}
