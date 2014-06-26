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

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getField($key)
    {
        if (!isset($this->fields[$key])) {
            return null;
        }

        return $this->fields[$key];
    }

    /**
     * @param mixed $offset
     * @return bool true on success or false on failure
     */
    public function offsetExists($offset)
    {
        return isset($this->fields[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getField($offset);
    }

    /**
     * @throws \BadMethodCallException
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('Cannot set a field using array access');
    }

    /**
     * @throws \BadMethodCallException
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Cannot unset a field');
    }
}
