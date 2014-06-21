<?php

namespace Markup\Contentful;

class Entry implements EntryInterface
{
    use MetadataAccessTrait;

    /**
     * @var array
     */
    private $fields;

    /**
     * @param array             $fields
     * @param MetadataInterface $metadata
     */
    public function __construct($fields, MetadataInterface $metadata)
    {
        $this->fields = $fields;
        $this->metadata = $metadata;
    }

    /**
     * Gets the list of field values in the entry, keyed by fields. Could be scalars, DateTime objects, or links.
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }
}
