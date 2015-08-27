<?php

namespace Markup\Contentful;

interface ContentTypeInterface extends ResourceInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * Returns the content type fields, keyed by ID.
     *
     * @return ContentTypeField[]
     */
    public function getFields();

    /**
     * @return ContentTypeField|null
     */
    public function getDisplayField();
}
