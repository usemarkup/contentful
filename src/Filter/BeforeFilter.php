<?php

namespace Markup\Contentful\Filter;

class BeforeFilter extends RelativeTimeFilter
{
    /**
     * The key in a query string on an API request.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->createLessThanFilter()->getKey();
    }

    /**
     * The value in a query string on an API request.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->createLessThanFilter()->getValue();
    }

    private function createLessThanFilter()
    {
        return new LessThanFilter(
            $this->getProperty(),
            new \DateTime($this->getRelativeTime())
        );
    }
}
