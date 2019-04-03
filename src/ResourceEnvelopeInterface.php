<?php

namespace Markup\Contentful;

/**
 * A envelope for stashing and accessing entries, assets and content types from a search on Contentful.
 */
interface ResourceEnvelopeInterface
{
    public function findEntry(string $entryId, ?string $locale = null): ?EntryInterface;

    public function hasEntry(string $entryId, ?string $locale = null): bool;

    public function findAsset(string $assetId, ?string $locale = null): ?AssetInterface;

    public function hasAsset(string $assetId, ?string $locale = null): bool;

    public function findContentType(string $contentTypeId): ?ContentTypeInterface;

    public function findContentTypeByName(string $contentTypeName): ?ContentTypeInterface;

    public function hasContentType(string $contentTypeId): bool;

    /**
     * @param ResourceInterface|ResourceArray $resource
     * @return $this
     */
    public function insert($resource): self;

    public function insertEntry(EntryInterface $entry): self;

    public function insertAsset(AssetInterface $asset): self;

    public function insertContentType(ContentTypeInterface $contentType): self;

    public function insertAllContentTypes(ResourceArray $contentTypes): self;

    public function getAllContentTypes(): ?ResourceArray;

    public function getEntryCount(): int;

    public function getAssetCount(): int;

    public function getContentTypeCount(): int;
}
