<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\FilterInterface;

class ContentTypeFilter implements FilterInterface
{
    const KEY = 'content_type';

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
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
        return $this->value;
    }
}
