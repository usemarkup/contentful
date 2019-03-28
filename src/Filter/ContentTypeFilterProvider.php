<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\Contentful;

/**
 * A provider object that can generate a content type filter given a content type name.
 */
class ContentTypeFilterProvider
{
    /**
     * @var Contentful
     */
    private $contentful;

    /**
     * @param Contentful $contentful
     */
    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    /**
     * @param string $contentTypeName
     * @param string $spaceName
     * @return ContentTypeFilter|null
     */
    public function createForContentTypeName($contentTypeName, $spaceName)
    {
        $contentType = $this->contentful->getContentTypeByName($contentTypeName, $spaceName);
        if (!$contentType) {
            return null;
        }

        return new ContentTypeFilter($contentType->getId());
    }
}
