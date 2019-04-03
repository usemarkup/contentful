<?php

namespace Markup\Contentful;

/**
 * A memoization envelope for stashing and accessing entries, assets and content types from a search on Contentful.
 */
class MemoizedResourceEnvelope implements ResourceEnvelopeInterface
{
    use ResourceInsertDelegationTrait;

    const WILDCARD_KEY = '*';

    /**
     * A list of entries keyed by locale, and then by ID.
     *
     * @var array
     */
    private $entries;

    /**
     * A list of assets keyed by ID.
     *
     * @var array
     */
    private $assets;

    /**
     * A list of content types keyed by ID.
     *
     * @var array
     */
    private $contentTypes;

    /**
     * A set of all known content types for specific groups.
     *
     * @var ResourceArray
     */
    private $contentTypeGroups;

    public function __construct()
    {
        $this->entries = [];
        $this->assets = [];
        $this->contentTypes = [];
    }

    /**
     * @param string $entryId
     * @param string|null $locale
     * @return EntryInterface|null
     */
    public function findEntry(string $entryId, ?string $locale = null): ?EntryInterface
    {
        $identifier = $this->getIdentifierForLocale($locale);
        if (!isset($this->entries[$identifier][$entryId])) {
            return null;
        }

        return $this->entries[$identifier][$entryId];
    }

    /**
     * @param string $entryId
     * @param string|null $locale
     * @return bool
     */
    public function hasEntry(string $entryId, ?string $locale = null): bool
    {
        $identifier = $this->getIdentifierForLocale($locale);

        return isset($this->entries[$identifier][$entryId]);
    }

    /**
     * @param string $assetId
     * @param string|null $locale
     * @return AssetInterface|null
     */
    public function findAsset(string $assetId, ?string $locale = null): ?AssetInterface
    {
        $identifier = $this->getIdentifierForLocale($locale);
        if (!isset($this->assets[$identifier][$assetId])) {
            return null;
        }

        return $this->assets[$identifier][$assetId];
    }

    /**
     * @param string $assetId
     * @param string|null $locale
     * @return bool
     */
    public function hasAsset(string $assetId, ?string $locale = null): bool
    {
        $identifier = $this->getIdentifierForLocale($locale);

        return isset($this->assets[$identifier][$assetId]);
    }

    /**
     * @param string $contentTypeId
     * @return ContentTypeInterface|null
     */
    public function findContentType(string $contentTypeId): ?ContentTypeInterface
    {
        if (!isset($this->contentTypes[$contentTypeId])) {
            return null;
        }

        return $this->contentTypes[$contentTypeId];
    }

    /**
     * @param string $contentTypeName
     * @return ContentTypeInterface|null
     */
    public function findContentTypeByName(string $contentTypeName): ?ContentTypeInterface
    {
        foreach ($this->contentTypes as $contentType) {
            if ($contentType->getName() === $contentTypeName) {
                return $contentType;
            }
        }

        return null;
    }

    /**
     * @param string $contentTypeId
     * @return bool
     */
    public function hasContentType(string $contentTypeId): bool
    {
        return isset($this->contentTypes[$contentTypeId]);
    }

    /**
     * @param EntryInterface $entry
     * @return $this
     */
    public function insertEntry(EntryInterface $entry): ResourceEnvelopeInterface
    {
        if (!isset($this->entries[self::WILDCARD_KEY][$entry->getId()])) {
            $this->entries[self::WILDCARD_KEY][$entry->getId()] = $entry;
        }
        if (null !== $entry->getLocale()) {
            $this->entries[$entry->getLocale()][$entry->getId()] = $entry;
        }

        return $this;
    }

    /**
     * @param AssetInterface $asset
     * @return $this
     */
    public function insertAsset(AssetInterface $asset): ResourceEnvelopeInterface
    {
        if (!isset($this->assets[self::WILDCARD_KEY][$asset->getId()])) {
            $this->assets[self::WILDCARD_KEY][$asset->getId()] = $asset;
        }
        if (null !== $asset->getLocale()) {
            $this->assets[$asset->getLocale()][$asset->getId()] = $asset;
        }

        return $this;
    }

    /**
     * @param ContentTypeInterface $contentType
     * @return $this
     */
    public function insertContentType(ContentTypeInterface $contentType): ResourceEnvelopeInterface
    {
        $this->contentTypes[$contentType->getId()] = $contentType;

        return $this;
    }

    /**
     * @param ResourceArray $contentTypes
     * @return $this
     */
    public function insertAllContentTypes(ResourceArray $contentTypes): ResourceEnvelopeInterface
    {
        $this->contentTypeGroups = $contentTypes;
        foreach ($contentTypes as $contentType) {
            /** @var ContentTypeInterface $contentType */
            $this->insertContentType($contentType);
        }

        return $this;
    }

    /**
     * Gets all content types for a given space if they are saved into the envelope, null otherwise.
     */
    public function getAllContentTypes(): ?ResourceArray
    {
        if (!isset($this->contentTypeGroups)) {
            return null;
        }

        return $this->contentTypeGroups;
    }

    /**
     * @return int
     */
    public function getEntryCount(): int
    {
        if (!isset($this->entries[self::WILDCARD_KEY])) {
            return 0;
        }

        return count($this->entries[self::WILDCARD_KEY]);
    }

    /**
     * @return int
     */
    public function getAssetCount(): int
    {
        if (!isset($this->assets[self::WILDCARD_KEY])) {
            return 0;
        }

        return count($this->assets[self::WILDCARD_KEY]);
    }

    /**
     * @return int
     */
    public function getContentTypeCount(): int
    {
        return count($this->contentTypes);
    }

    /**
     * @param string|null $locale
     * @return string
     */
    private function getIdentifierForLocale(?string $locale): string
    {
        return $locale ?: self::WILDCARD_KEY;
    }
}
