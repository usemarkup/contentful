<?php

namespace Markup\Contentful\Property;

use Markup\Contentful\PropertyInterface;

class Locale implements PropertyInterface
{
    const KEY = 'locale';

    /**
     * Gets the key to use against a Contentful API.
     *
     * @return string
     */
    public function getKey()
    {
        return self::KEY;
    }

    /**
     * Cast to string, using the key to use against a Contentful API.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getKey();
    }
}
