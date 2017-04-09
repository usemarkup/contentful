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
     * Returns the content type field matching the passed ID, or null if field does not exist.
     *
     * @param string $fieldId
     * @return ContentTypeField|null
     */
    public function getField($fieldId);

    /**
     * @return ContentTypeField|null
     */
    public function getDisplayField();
}
