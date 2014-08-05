<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\PropertyInterface;

abstract class AbstractComparisonFilter extends PropertyFilter
{
    use SimpleValueTrait, ClassBasedNameTrait;

    /**
     * @param PropertyInterface $property
     * @param string            $value
     */
    public function __construct(PropertyInterface $property, $value)
    {
        parent::__construct($property);
        $this->value = $value;
    }

    public function getKey()
    {
        return sprintf('%s[%s]', $this->getProperty()->getKey(), $this->getOperator());
    }

    abstract protected function getOperator();
}
