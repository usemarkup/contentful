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
} 
