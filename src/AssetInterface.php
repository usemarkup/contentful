<?php

namespace Markup\Contentful;

interface AssetInterface extends ResourceInterface
{
    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getFilename();

    /**
     * Gets the content type of the asset. This is a MIME type, *not* a Contentful type.
     *
     * @return string
     */
    public function getContentType();

    /**
     * @param array|ImageApiOptions $options Options for rendering the image using the Image API @see http://docs.contentfulimagesapi.apiary.io/
     * @return string
     */
    public function getUrl($imageApiOptions = null);

    /**
     * @return array
     */
    public function getDetails();

    /**
     * @return int
     */
    public function getFileSizeInBytes();

    /**
     * @return int|null
     */
    public function getWidth();

    /**
     * @return int|null
     */
    public function getHeight();

    /**
     * @return float|null
     */
    public function getRatio();
}
