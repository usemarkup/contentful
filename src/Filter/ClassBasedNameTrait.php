<?php

namespace Markup\Contentful\Filter;

trait ClassBasedNameTrait
{
    /**
     * Gets the name for the filter.
     *
     * @return string
     */
    public function getName()
    {
        //generate snake case name based on class name
        preg_match('/\\\?(\w+)Filter$/', get_class($this), $matches);

        return ltrim(strtolower(strval(preg_replace('/[A-Z]/', '_$0', $matches[1]))), '_');
    }
}
