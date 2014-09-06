<?php

namespace Markup\Contentful\Parameter;

use Markup\Contentful\ParameterInterface;

/**
 * A parameter representing a skip (offset) for a query.
 */
class Skip implements ParameterInterface
{
    const KEY = 'skip';

    /**
     * @var int $skip
     */
    private $skip;

    /**
     * @param int $skip
     */
    public function __construct($skip)
    {
        $this->skip = intval($skip);
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
        return $this->skip;
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
