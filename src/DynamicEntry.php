<?php

namespace Markup\Contentful;

/**
 * A class that decorates an entry and coerces its fields into types determined by its content type.
 */
class DynamicEntry implements EntryInterface
{
    use DisallowArrayAccessMutationTrait;
    use EntryUnknownMethodTrait;

    /**
     * @var EntryInterface
     */
    private $entry;

    /**
     * @var ContentTypeInterface
     */
    private $contentType;

    public function __construct(EntryInterface $entry, ContentTypeInterface $contentType)
    {
        $this->entry = $entry;
        $this->contentType = $contentType;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        foreach ($this->contentType->getFields() as $field) {
            if ($offset === $field->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->getField($offset);
    }

    /**
     * Gets the list of field values in the entry, keyed by fields. Could be scalars, DateTime objects, or links.
     *
     * @return array
     */
    public function getFields()
    {
        $coercedFields = [];
        foreach (array_keys($this->entry->getFields()) as $key) {
            $coercedFields[$key] = $this->getCoercedField($key);
        }

        return $coercedFields;
    }

    /**
     * Gets an individual field value, or null if the field is not defined.
     *
     * @return mixed
     */
    public function getField($key)
    {
        return $this->getCoercedField($key);
    }

    /**
     * Gets the type of resource.
     *
     * @return string
     */
    public function getType()
    {
        return $this->entry->getType();
    }

    /**
     * Gets the unique ID of the resource.
     *
     * @return string
     */
    public function getId()
    {
        return $this->entry->getId();
    }

    /**
     * Gets the space this resource is associated with.
     *
     * @return SpaceInterface
     */
    public function getSpace()
    {
        return $this->entry->getSpace();
    }

    /**
     * Gets the content type for an entry. (Only applicable for Entry resources.)
     *
     * @return ContentTypeInterface|null
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Gets the link type. (Only applicable for Link resources.)
     *
     * @return string|null
     */
    public function getLinkType()
    {
        return $this->entry->getLinkType();
    }

    /**
     * Gets the revision number of this resource.
     *
     * @return int
     */
    public function getRevision()
    {
        return $this->entry->getRevision();
    }

    /**
     * The time this resource was created.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->entry->getCreatedAt();
    }

    /**
     * The time this resource was last updated.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->entry->getUpdatedAt();
    }

    private function getCoercedField($key)
    {
        $contentTypeField = $this->contentType->getField($key);
        $raw = $this->entry->getField($key);
        if (!$contentTypeField) {
            return $raw;
        }
        switch ($contentTypeField->getType()) {
            case 'Symbol':
            case 'Text':
                return $raw;
            case 'Integer':
                return intval($raw);
            case 'Number':
            case 'Float':
                return floatval($raw);
            case 'Boolean':
                return (bool) $raw;
            case 'Date':
                return new \DateTime($raw, new \DateTimeZone('UTC'));
            case 'Location':
                list($lat, $lon) = explode(',', $raw);
                return new Location(floatval($lat), floatval($lon));
            default:
                return $raw;
        }
    }
}
