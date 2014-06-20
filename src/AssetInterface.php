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
     * @return string
     */
    public function getUrl();

    /**
     * @return array
     */
    public function getDetails();

    /**
     * @return int
     */
    public function getFileSizeInBytes();
}

