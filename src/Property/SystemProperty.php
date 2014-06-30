<?php

namespace Markup\Contentful\Property;

use Markup\Contentful\PropertyInterface;

class SystemProperty implements PropertyInterface
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
     * @return string
     */
    public function getKey()
    {
        return sprintf('sys.%s', $this->name);
    }

    public function __toString()
    {
        return $this->getKey();
    }
}
