<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\FilterInterface;
use Markup\Contentful\Property\SystemProperty;

class IsArchivedFilter implements FilterInterface
{
    use ClassBasedNameTrait;

    /**
     * @var FilterInterface
     */
    private $filter;

    /**
     * @param bool $value
     */
    public function __construct($value)
    {
        $this->filter = new ExistsFilter(new SystemProperty('archivedVersion'), $value);
    }

    /**
     * The key in a query string on an API request.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->filter->getKey();
    }

    /**
     * The value in a query string on an API request.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->filter->getValue();
    }
}
