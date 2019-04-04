<?php

namespace Markup\Contentful\Promise;

use GuzzleHttp\Promise\PromiseInterface;
use Markup\Contentful\ContentType;
use Markup\Contentful\ContentTypeFieldInterface;
use Markup\Contentful\ContentTypeInterface;
use Markup\Contentful\Metadata;

class ContentTypePromise extends ResourcePromise implements ContentTypeInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof ContentTypeInterface) {
            return '';
        }

        return $resolved->getName();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof ContentTypeInterface) {
            return '';
        }

        return $resolved->getDescription();
    }

    /**
     * Returns the content type fields, keyed by ID.
     *
     * @return ContentTypeFieldInterface[]
     */
    public function getFields()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof ContentTypeInterface) {
            return [];
        }

        return $resolved->getFields();
    }

    /**
     * Returns the content type field matching the passed ID, or null if field does not exist.
     *
     * @param string $fieldId
     * @return ContentTypeFieldInterface|null
     */
    public function getField($fieldId)
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof ContentTypeInterface) {
            return null;
        }

        return $resolved->getField($fieldId);
    }

    /**
     * @return ContentTypeFieldInterface|null
     */
    public function getDisplayField()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof ContentTypeInterface) {
            return null;
        }

        return $resolved->getDisplayField();
    }

    /**
     * @param PromiseInterface $promise
     * @return PromiseInterface
     */
    protected function addRejectionHandlerToPromise(PromiseInterface $promise)
    {
        return $promise
            ->otherwise(function ($reason) {
                return new ContentType('', '', [], new Metadata());
            });
    }
}
