<?php

namespace Markup\Contentful\Parameter;

use Markup\Contentful\ParameterInterface;
use Markup\Contentful\PropertyInterface;

/**
 * An order (sort) parameter on a query.
 */
class OrderBy implements ParameterInterface
{
    const KEY = 'order';

    /**
     * @var PropertyInterface
     */
    private $property;

    /**
     * @var string
     */
    private $direction;

    /**
     * @param PropertyInterface $property
     * @param string $direction The direction of sort - either SORT_ASC or SORT_DESC
     */
    public function __construct(PropertyInterface $property, $direction = SORT_ASC)
    {
        $this->property = $property;
        $this->direction = ($direction === SORT_DESC) ? SORT_DESC : SORT_ASC;
    }

    /**
     * The key in a query string on an API request.
     *
     * @return string
     */
    public function getKey()
    {
        return self::KEY;
    }

    /**
     * The value in a query string on an API request.
     *
     * @return string
     */
    public function getValue()
    {
        $prefix = ($this->isDescending()) ? '-' : '';

        return $prefix . $this->property->getKey();
    }

    /**
     * The name for the parameter (e.g. an EqualFilter would be called 'equal', etc)
     *
     * @return string
     */
    public function getName()
    {
        return self::KEY;
    }

    private function isDescending()
    {
        return $this->direction === SORT_DESC;
    }
}
