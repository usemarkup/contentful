<?php

namespace Markup\Contentful;

/**
 * An individual parameter for a query.
 */
interface ParameterInterface
{
    /**
     * The key in a query string on an API request.
     *
     * @return string
     */
    public function getKey();

    /**
     * The value in a query string on an API request.
     *
     * @return string
     */
    public function getValue();

    /**
     * The name for the parameter (e.g. an EqualFilter would be called 'equal', etc)
     *
     * @return string
     */
    public function getName();
} 
