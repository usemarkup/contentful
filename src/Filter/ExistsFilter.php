<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\PropertyInterface;

class ExistsFilter extends PropertyFilter
{
    use ClassBasedNameTrait;

    /**
     * @var bool
     */
    private $value;

    /**
     * @param PropertyInterface $property
     * @param bool              $value
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
        return sprintf('%s[exists]', $this->getProperty()->getKey());
    }

    /**
     * The value in a query string on an API request.
     *
     * @return string
     */
    public function getValue()
    {
        return (bool) $this->value;
    }
}
