<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\Property\Locale;

class LocaleFilter extends EqualFilter
{
    /**
     * @param string $localeString
     */
    public function __construct($localeString)
    {
        parent::__construct(new Locale(), $localeString);
    }
}
