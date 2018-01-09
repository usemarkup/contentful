<?php

namespace Markup\Contentful\Promise;

use GuzzleHttp\Promise\PromiseInterface;
use Markup\Contentful\Asset;
use Markup\Contentful\AssetInterface;
use Markup\Contentful\ImageApiOptions;
use Markup\Contentful\Metadata;

/**
 * An asset implementation that is also a promise allowing deferred or pooled fetching of the underlying data.
 */
class AssetPromise extends ResourcePromise implements AssetInterface
{
    /**
     * @return string
     */
    public function getTitle()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof AssetInterface) {
            return '';
        }

        return $resolved->getTitle();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof AssetInterface) {
            return '';
        }

        return $resolved->getDescription();
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof AssetInterface) {
            return '';
        }

        return $resolved->getFilename();
    }

    public function getMimeType()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof AssetInterface) {
            return '';
        }

        return $resolved->getMimeType();
    }

    /**
     * @param array|ImageApiOptions $imageApiOptions Options for rendering the image using the Image API @see http://docs.contentfulimagesapi.apiary.io/
     * @return string
     */
    public function getUrl($imageApiOptions = null)
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof AssetInterface) {
            return '';
        }

        return $resolved->getUrl($imageApiOptions);
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof AssetInterface) {
            return [];
        }

        return $resolved->getDetails();
    }

    /**
     * @return int
     */
    public function getFileSizeInBytes()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof AssetInterface) {
            return 0;
        }

        return $resolved->getFileSizeInBytes();
    }

    /**
     * @param PromiseInterface $promise
     * @return PromiseInterface
     */
    protected function addRejectionHandlerToPromise(PromiseInterface $promise)
    {
        return $promise
            ->otherwise(function ($reason) {
                return new Asset('', '', null, new Metadata());
            });
    }

    /**
     * @return int|null
     */
    public function getWidth()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof AssetInterface) {
            return null;
        }

        return $resolved->getWidth();
    }

    /**
     * @return int|null
     */
    public function getHeight()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof AssetInterface) {
            return null;
        }

        return $resolved->getHeight();
    }

    /**
     * @return float|null
     */
    public function getRatio()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof AssetInterface) {
            return null;
        }

        return $resolved->getRatio();
    }
}
