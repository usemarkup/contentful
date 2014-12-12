<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\IncompleteParameterInterface;

/**
 * A filter that uses a content type's name. Incomplete as the content type's ID needs to be resolved separately.
 */
class ContentTypeNameFilter implements IncompleteParameterInterface
{
    use ClassBasedNameTrait, IncompleteTrait;

    /**
     * @var string
     */
    private $contentTypeName;

    /**
     * @param string $contentTypeName
     */
    public function __construct($contentTypeName)
    {
        $this->contentTypeName = $contentTypeName;
    }

    /**
     * The value in a query string on an API request.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->contentTypeName;
    }
}
