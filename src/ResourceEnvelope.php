<?php

namespace Markup\Contentful;

/**
 * An envelope for stashing and accessing entries, assets and content types from a search on Contentful.
 */
class ResourceEnvelope
{
    /**
     * A list of entries keyed by ID.
     *
     * @var EntryInterface[]
     */
    private $entries;

    /**
     * A list of assets keyed by ID.
     *
     * @var AssetInterface[]
     */
    private $assets;

    /**
     * A list of content types keyed by ID.
     *
     * @var ContentTypeInterface[]
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
     * @return EntryInterface|null
     */
    public function findEntry($entryId)
    {
        if (!isset($this->entries[$entryId])) {
            return null;
        }

        return $this->entries[$entryId];
    }

    /**
     * @param string $entryId
     * @return bool
     */
    public function hasEntry($entryId)
    {
        return isset($this->entries[$entryId]);
    }

    /**
     * @param string $assetId
     * @return AssetInterface|null
     */
    public function findAsset($assetId)
    {
        if (!isset($this->assets[$assetId])) {
            return null;
        }

        return $this->assets[$assetId];
    }

    /**
     * @param string $assetId
     * @return bool
     */
    public function hasAsset($assetId)
    {
        return isset($this->assets[$assetId]);
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
     * @return self
     */
    public function insertEntry(EntryInterface $entry)
    {
        $this->entries[$entry->getId()] = $entry;

        return $this;
    }

    /**
     * @param AssetInterface $asset
     * @return self
     */
    public function insertAsset(AssetInterface $asset)
    {
        $this->assets[$asset->getId()] = $asset;

        return $this;
    }

    /**
     * @param ContentTypeInterface $contentType
     * @return self
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
        return count($this->entries);
    }

    /**
     * @return int
     */
    public function getAssetCount()
    {
        return count($this->assets);
    }

    /**
     * @return int
     */
    public function getContentTypeCount()
    {
        return count($this->contentTypes);
    }
}
