<?php

namespace Markup\Contentful;

class Asset implements AssetInterface
{
    use MetadataAccessTrait;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var AssetFile
     */
    private $assetFile;

    /**
     * @param string $title
     * @param string $description
     * @param AssetFile $assetFile
     * @param MetadataInterface $metadata
     */
    public function __construct($title, $description, AssetFile $assetFile = null, MetadataInterface $metadata)
    {
        $this->title = $title;
        $this->description = $description;
        $this->assetFile = $assetFile;
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        $file = $this->getFile();
        if (!$file) {
            return null;
        }

        return $file->getFilename();
    }

    /**
     * Gets the content type of the asset. This is a MIME type, *not* a Contentful type.
     *
     * @return string
     */
    public function getContentType()
    {
        $file = $this->getFile();
        if (!$file) {
            return null;
        }

        return $file->getContentType();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $file = $this->getFile();
        if (!$file) {
            return null;
        }
        return $file->getUrl();
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        $file = $this->getFile();
        if (!$file) {
            return [];
        }

        return $file->getDetails();
    }

    /**
     * @return int
     */
    public function getFileSizeInBytes()
    {
        $file = $this->getFile();
        if (!$file) {
            return null;
        }

        return $file->getFileSizeInBytes();
    }

    /**
     * @return AssetFile|null
     */
    private function getFile()
    {
        return $this->assetFile;
    }
}
