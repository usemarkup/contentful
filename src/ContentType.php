<?php

namespace Markup\Contentful;

class ContentType implements ContentTypeInterface
{
    use MetadataAccessTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * The fields, keyed by ID.
     *
     * @var ContentTypeField[]
     */
    private $fields;

    /**
     * @var string
     */
    private $displayField;

    /**
     * @param string             $name
     * @param string             $description
     * @param ContentTypeField[] $fields
     * @param MetadataInterface  $metadata
     * @param ContentTypeField   $displayField
     */
    public function __construct($name, $description, $fields, MetadataInterface $metadata, $displayField = null)
    {
        $this->name = $name;
        $this->metadata = $metadata;
        $this->description = $description;
        $this->fields = [];
        foreach ($fields as $field) {
            $this->fields[$field->getId()] = $field;
        }
        $this->displayField = $displayField;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return ContentTypeField[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $fieldId
     * @return ContentTypeField
     */
    public function getField($fieldId)
    {
        if (!isset($this->fields[$fieldId])) {
            return null;
        }

        return $this->fields[$fieldId];
    }

    /**
     * @return \Markup\Contentful\ContentTypeField
     */
    public function getDisplayField()
    {
        if (null === $this->displayField) {
            return null;
        }

        return $this->getField($this->displayField);
    }
}
