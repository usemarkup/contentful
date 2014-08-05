<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\FilterInterface;
use Markup\Contentful\Location;
use Markup\Contentful\PropertyInterface;

class WithinRectangleFilter implements FilterInterface
{
    use ClassBasedNameTrait;

    /**
     * @var PropertyInterface
     */
    private $property;

    /**
     * @var Location
     */
    private $bottomLeft;

    /**
     * @var Location
     */
    private $topRight;

    /**
     * @param PropertyInterface $property
     * @param Location          $bottomLeft
     * @param Location          $topRight
     */
    public function __construct(PropertyInterface $property, Location $bottomLeft, Location $topRight)
    {
        $this->property = $property;
        $this->bottomLeft = $bottomLeft;
        $this->topRight = $topRight;
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
                $this->bottomLeft->getLatitude(),
                $this->bottomLeft->getLongitude(),
                $this->topRight->getLatitude(),
                $this->topRight->getLongitude(),
            ]
        );
    }
}
