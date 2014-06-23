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
    public function __construct($title, $description, AssetFile $assetFile, MetadataInterface $metadata)
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
        return $this->getFile()->getFilename();
    }

    /**
     * Gets the content type of the asset. This is a MIME type, *not* a Contentful type.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->getFile()->getContentType();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->getFile()->getUrl();
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->getFile()->getDetails();
    }

    /**
     * @return int
     */
    public function getFileSizeInBytes()
    {
        return $this->getFile()->getFileSizeInBytes();
    }

    /**
     * @return AssetFile
     */
    private function getFile()
    {
        return $this->assetFile;
    }
}
