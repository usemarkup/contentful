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
     * @param MetadataInterface  $metadata
     * @param string             $description
     * @param ContentTypeField[] $fields
     * @param ContentTypeField   $displayField
     */
    public function __construct($name, MetadataInterface $metadata, $description, $fields, ContentTypeField $displayField)
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
        return $this->fields;
    }

    /**
     * @return \Markup\Contentful\ContentTypeField
     */
    public function getDisplayField()
    {
        return $this->displayField;
    }
}
