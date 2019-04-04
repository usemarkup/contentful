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
     * @var ContentTypeFieldInterface[]
     */
    private $fields;

    /**
     * @var string|null
     */
    private $displayField;

    /**
     * @param string                      $name
     * @param string                      $description
     * @param ContentTypeFieldInterface[] $fields
     * @param MetadataInterface           $metadata
     * @param string                      $displayField
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
     * @return ContentTypeFieldInterface[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $fieldId
     * @return ContentTypeFieldInterface|null
     */
    public function getField($fieldId)
    {
        if (!isset($this->fields[$fieldId])) {
            return null;
        }

        return $this->fields[$fieldId];
    }

    /**
     * @return ContentTypeFieldInterface|null
     */
    public function getDisplayField()
    {
        if (null === $this->displayField) {
            return null;
        }

        return $this->getField($this->displayField);
    }
}
