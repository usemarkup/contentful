<?php

namespace Markup\Contentful\Filter;

trait SimpleValueTrait
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * The value in a query string on an API request.
     *
     * @return string
     */
    public function getValue()
    {
        if ($this->value instanceof \DateTime) {
            $this->value->setTimezone(new \DateTimeZone('UTC'));

            return $this->value->format('Y-m-d\TH:i:s\Z');
        }

        return $this->value;
    }
}
