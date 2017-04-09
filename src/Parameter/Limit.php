<?php

namespace Markup\Contentful\Parameter;

use Markup\Contentful\ParameterInterface;

/**
 * A parameter representing a limit for a query.
 */
class Limit implements ParameterInterface
{
    const KEY = 'limit';

    /**
     * @var int $limit
     */
    private $limit;

    /**
     * @param int $limit
     */
    public function __construct($limit)
    {
        $this->limit = intval($limit);
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
        return (string) $this->limit;
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
