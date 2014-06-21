<?php

namespace Markup\Contentful;

class Link implements MetadataInterface
{
    use MetadataAccessTrait;

    /**
     * @param MetadataInterface $metadata
     */
    public function __construct(MetadataInterface $metadata)
    {
        $this->metadata = $metadata;
    }
}
