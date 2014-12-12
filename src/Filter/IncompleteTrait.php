<?php

namespace Markup\Contentful\Filter;

trait IncompleteTrait
{
    /**
     * @throws \LogicException
     */
    public function getKey()
    {
        throw new \LogicException(sprintf('This parameter with name "%s" is incomplete and cannot be used directly.', $this->getName()));
    }
}
