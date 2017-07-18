<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\PropertyInterface;

abstract class RelativeTimeFilter extends PropertyFilter implements DecidesCacheKeyInterface
{
    use ClassBasedNameTrait;

    /**
     * @var string
     */
    private $relativeTime;

    /**
     * @param PropertyInterface $property
     * @param string            $relativeTime
     */
    public function __construct(PropertyInterface $property, $relativeTime)
    {
        parent::__construct($property);
        $this->relativeTime = $relativeTime;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return sprintf(
            '|%s|%s↦%s',
            $this->getName(),
            $this->getProperty()->getKey(),
            str_replace(' ', '_', $this->relativeTime)
        );
    }

    protected function getRelativeTime()
    {
        return $this->relativeTime;
    }
}
