<?php

namespace Markup\Contentful\Filter;

/**
 * Interface to be implemented by a filter when that filter has a custom means of deciding how it is represented
 * in a query cache key.
 */
interface DecidesCacheKeyInterface
{
    /**
     * @return string
     */
    public function getCacheKey();
}
