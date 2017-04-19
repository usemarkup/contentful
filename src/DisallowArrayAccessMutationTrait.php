<?php

namespace Markup\Contentful;

/**
 * Trait to ensure mutating array access usage is prevented.
 */
trait DisallowArrayAccessMutationTrait
{
    /**
     * @throws \BadMethodCallException
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('Cannot set a value using array access');
    }

    /**
     * @throws \BadMethodCallException
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Cannot unset a value');
    }
}
