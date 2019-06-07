<?php

namespace Markup\Contentful;

class Link implements LinkInterface, MetadataInterface
{
    use MetadataAccessTrait;

    /**
     * An explicit space ID that this link is declared as belonging to.
     *
     * @var string
     */
    private $spaceName;

    public function __construct(MetadataInterface $metadata, string $spaceName)
    {
        $this->metadata = $metadata;
        $this->spaceName = $spaceName;
    }

    /**
     * Gets a space name (not intrinsic, but how the space is referred to in configuration) that this link is associated with, if available.
     */
    public function getSpaceName(): string
    {
        return $this->spaceName;
    }
}
