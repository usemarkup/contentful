<?php

namespace Markup\Contentful\Filter;

class AfterFilter extends RelativeTimeFilter
{
    /**
     * The key in a query string on an API request.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->createGreaterThanFilter()->getKey();
    }

    /**
     * The value in a query string on an API request.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->createGreaterThanFilter()->getValue();
    }

    private function createGreaterThanFilter()
    {
        return new GreaterThanFilter(
            $this->getProperty(),
            new \DateTime($this->getRelativeTime())
        );
    }
}
