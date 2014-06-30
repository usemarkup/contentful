<?php

namespace Markup\Contentful;

/**
 * An envelope for stashing and accessing included entries and assets from a search on Contentful.
 */
class IncludesEnvelope
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
} 
