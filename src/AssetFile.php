<?php

namespace Markup\Contentful;

class AssetFile
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var array
     */
    private $details;

    /**
     * @var string
     */
    private $url;

    /**
     * @param string $filename
     * @param string $contentType
     * @param array  $details
     * @param string $url
     */
    public function __construct($filename, $contentType, $details, $url)
    {
        $this->filename = $filename;
        $this->contentType = $contentType;
        $this->details = $details;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return int|null
     */
    public function getFileSizeInBytes()
    {
        $details = $this->getDetails();
        if (!isset($details['size'])) {
            return null;
        }

        return intval($details['size']);
    }

    /**
     * @return int|null
     */
    public function getWidth()
    {
        $details = $this->getDetails();
        if (!isset($details['width'])) {
            return null;
        }

        return intval($details['width']);
    }

    /**
     * @return int|null
     */
    public function getHeight()
    {
        $details = $this->getDetails();
        if (!isset($details['height'])) {
            return null;
        }

        return intval($details['height']);
    }

    /**
     * @return float|null
     */
    public function getRatio()
    {
        $width = $this->getWidth();
        $height = $this->getHeight();
        if (!$width || !$height) {
            return null;
        }

        return (float) $width/$height;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
