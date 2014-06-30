<?php

namespace Markup\Contentful;

interface PropertyInterface
{
    /**
     * Gets the key to use against a Contentful API.
     *
     * @return string
     */
    public function getKey();

    /**
     * Cast to string, using the key to use against a Contentful API.
     *
     * @return string
     */
    public function __toString();
}
