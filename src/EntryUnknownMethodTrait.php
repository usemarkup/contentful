<?php

namespace Markup\Contentful;

trait EntryUnknownMethodTrait
{
    public function __call($method, $args)
    {
        return $this->getField($method);
    }
}
