<?php
declare(strict_types=1);

namespace Markup\Contentful\Property;

use Markup\Contentful\PropertyInterface;

class FullTextFieldProperty implements PropertyInterface
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Gets the key to use against a Contentful API.
     */
    public function getKey(): string
    {
        return sprintf('fields.%s[match]', $this->name);
    }

    /**
     * Cast to string, using the key to use against a Contentful API.
     */
    public function __toString(): string
    {
        return $this->getKey();
    }
}
