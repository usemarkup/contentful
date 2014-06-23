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
     * @var ContentTypeField[]
     */
    private $fields;

    /**
     * @var ContentTypeField
     */
    private $displayField;

    /**
     * @param string             $name
     * @param string             $description
     * @param ContentTypeField[] $fields
     * @param MetadataInterface  $metadata
     * @param ContentTypeField   $displayField
     */
    public function __construct($name, $description, $fields, MetadataInterface $metadata, ContentTypeField $displayField = null)
    {
        $this->name = $name;
        $this->metadata = $metadata;
        $this->description = $description;
        $this->fields = $fields;
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
     * @return \Markup\Contentful\ContentTypeField[]
     */
    public function getFields()
    {
        $fields = [];
        foreach ($this->fields as $field) {
            $fields[$field->getId()] = $field;
        }

        return $fields;
    }

    /**
     * @return \Markup\Contentful\ContentTypeField
     */
    public function getDisplayField()
    {
        return $this->displayField;
    }
}
