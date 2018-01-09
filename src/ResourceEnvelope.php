<?php

namespace Markup\Contentful;

/**
 * An envelope for stashing and accessing entries, assets and content types from a search on Contentful.
 */
class ResourceEnvelope
{
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
     */
    private $contentTypeGroups;

    public function __construct()
    {
        $this->entries = [];
        $this->assets = [];
        $this->contentTypes = [];
        $this->contentTypeGroups = [];
    }

    /**
     * @param string $entryId
     * @param string|null $locale
     * @return EntryInterface|null
     */
    public function findEntry($entryId, $locale = null)
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
    public function hasEntry($entryId, $locale = null)
    {
        $identifier = $this->getIdentifierForLocale($locale);

        return isset($this->entries[$identifier][$entryId]);
    }

    /**
     * @param string $assetId
     * @param string|null $locale
     * @return AssetInterface|null
     */
    public function findAsset($assetId, $locale = null)
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
    public function hasAsset($assetId, $locale = null)
    {
        $identifier = $this->getIdentifierForLocale($locale);

        return isset($this->assets[$identifier][$assetId]);
    }

    /**
     * @param string $contentTypeId
     * @return ContentTypeInterface|null
     */
    public function findContentType($contentTypeId)
    {
        if (!isset($this->contentTypes[$contentTypeId])) {
            return null;
        }

        return $this->contentTypes[$contentTypeId];
    }

    /**
     * @param string $contentTypeName
     * @return ContentTypeInterface|mixed|null
     */
    public function findContentTypeByName($contentTypeName)
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
    public function hasContentType($contentTypeId)
    {
        return isset($this->contentTypes[$contentTypeId]);
    }

    /**
     * @param ResourceInterface|ResourceArray $resource
     * @return $this
     */
    public function insert($resource)
    {
        if ($resource instanceof ResourceArray) {
            foreach ($resource as $resourceItem) {
                $this->insert($resourceItem);
            }
        }
        if ($resource instanceof EntryInterface) {
            return $this->insertEntry($resource);
        }
        if ($resource instanceof AssetInterface) {
            return $this->insertAsset($resource);
        }
        if ($resource instanceof ContentTypeInterface) {
            return $this->insertContentType($resource);
        }

        return $this;
    }

    /**
     * @param EntryInterface $entry
     * @return $this
     */
    public function insertEntry(EntryInterface $entry)
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
    public function insertAsset(AssetInterface $asset)
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
    public function insertContentType(ContentTypeInterface $contentType)
    {
        $this->contentTypes[$contentType->getId()] = $contentType;

        return $this;
    }

    /**
     * @param ContentTypeInterface[] $contentTypes
     * @param string $space
     */
    public function insertAllContentTypesForSpace($contentTypes, $space)
    {
        $this->contentTypeGroups[$space] = $contentTypes;
        foreach ($contentTypes as $contentType) {
            $this->insertContentType($contentType);
        }

        return $this;
    }

    /**
     * Gets all content types for a given space if they are saved into the envelope, null otherwise.
     *
     * @param string $space
     * @return ContentTypeInterface[]|null
     */
    public function getAllContentTypesForSpace($space)
    {
        if (!isset($this->contentTypeGroups[$space])) {
            return null;
        }

        return $this->contentTypeGroups[$space];
    }

    /**
     * @return int
     */
    public function getEntryCount()
    {
        if (!isset($this->entries[self::WILDCARD_KEY])) {
            return 0;
        }

        return count($this->entries[self::WILDCARD_KEY]);
    }

    /**
     * @return int
     */
    public function getAssetCount()
    {
        if (!isset($this->assets[self::WILDCARD_KEY])) {
            return 0;
        }

        return count($this->assets[self::WILDCARD_KEY]);
    }

    /**
     * @return int
     */
    public function getContentTypeCount()
    {
        return count($this->contentTypes);
    }

    /**
     * @param string|null $locale
     * @return string
     */
    private function getIdentifierForLocale($locale)
    {
        return $locale ?: self::WILDCARD_KEY;
    }
}
