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
     * @return string|null
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
     * Gets the "contentType" of the asset. This is a MIME type, *not* a Contentful content type.
     *
     * @return string|null
     */
    public function getMimeType()
    {
        $file = $this->getFile();
        if (!$file) {
            return null;
        }

        return $file->getContentType();
    }

    /**
     * @param array|ImageApiOptions $imageApiOptions Options for rendering the image using the Image API @see http://docs.contentfulimagesapi.apiary.io/
     * @return string|null
     */
    public function getUrl($imageApiOptions = null)
    {
        $file = $this->getFile();
        if (!$file) {
            return null;
        }
        $url = $file->getUrl();
        $urlContainsQueryString = function ($url) {
            return strlen(parse_url($url, PHP_URL_QUERY)) > 0;
        };
        if ($imageApiOptions) {
            $apiOptions = ($imageApiOptions instanceof ImageApiOptions)
                ? $imageApiOptions
                : ImageApiOptions::createFromHumanOptions($imageApiOptions);
            $apiQueryString = http_build_query($apiOptions->toArray());
            if (strlen($apiQueryString) > 0) {
                if (!$urlContainsQueryString($url)) {
                    $url .= '?';
                } else {
                    $url .= '&';
                }
                $url .= $apiQueryString;
            }
        }

        return $url;
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
     * @return int|null
     */
    public function getWidth()
    {
        $file = $this->getFile();
        if (!$file) {
            return null;
        }

        return $file->getWidth();
    }

    /**
     * @return int|null
     */
    public function getHeight()
    {
        $file = $this->getFile();
        if (!$file) {
            return null;
        }

        return $file->getHeight();
    }

    /**
     * @return float|null
     */
    public function getRatio()
    {
        $file = $this->getFile();
        if (!$file) {
            return null;
        }

        return $file->getRatio();
    }

    /**
     * @return int|null
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
