<?php

namespace Markup\Contentful\Property;

use Markup\Contentful\PropertyInterface;

class FieldProperty implements PropertyInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Gets the key to use against a Contentful API.
     *
     * @return string
     */
    public function getKey()
    {
        return sprintf('fields.%s', $this->name);
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
