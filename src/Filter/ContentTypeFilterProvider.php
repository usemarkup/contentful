<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\Contentful;
use Markup\Contentful\ContentType;
use Markup\Contentful\ContentTypeInterface;

/**
 * A provider object that can generate a content type filter given a content type name.
 */
class ContentTypeFilterProvider
{
    /**
     * @var Contentful
     */
    private $contentful;

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
        /** @var ContentTypeInterface|null $contentType */
        $contentType = $this->contentful->getContentTypeByName($contentTypeName, $spaceName);
        if (!$contentType) {
            return null;
        }

        return new ContentTypeFilter($contentType->getId());
    }
}
