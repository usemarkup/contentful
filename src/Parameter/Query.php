<?php

namespace Markup\Contentful\Parameter;

use Markup\Contentful\ParameterInterface;

/**
 * A parameter representing a query.
 */
class Query implements ParameterInterface
{
    const KEY = 'query';

    /**
     * @var string $query
     */
    private $query;

    /**
     * @param string $query
     */
    public function __construct($query)
    {
        $this->query = (string)$query;
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
        return $this->query;
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
}
