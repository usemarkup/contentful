<?php

namespace Markup\Contentful;

interface FilterInterface
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
     * The name for the filter (e.g. an EqualFilter would be called 'equal', etc)
     *
     * @return string
     */
    public function getName();
} 
