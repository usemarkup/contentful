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
     * @var array
     */
    private $resolvedLinks;

    /**
     * @var callable
     */
    private $resolveLinkFunction;

    /**
     * @param array             $fields
     * @param MetadataInterface $metadata
     */
    public function __construct($fields, MetadataInterface $metadata)
    {
        $this->fields = $fields;
        $this->metadata = $metadata;
        $this->resolvedLinks = [];
    }

    /**
     * Gets the list of field values in the entry, keyed by fields. Could be scalars, DateTime objects, or links. Links will not resolve or be resolved.
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
        if ($key === 'bestFriend') {

        }
        if (!isset($this->fields[$key])) {
            return null;
        }
        if (null !== $this->resolveLinkFunction) {
            if ($this->fields[$key] instanceof Link) {
                if (!isset($this->resolvedLinks[$key])) {
                    $this->resolvedLinks[$key] = call_user_func($this->resolveLinkFunction, $this->fields[$key]);
                }

                return $this->resolvedLinks[$key];
            }
            if (is_array($this->fields[$key]) && array_values($this->fields[$key])[0] instanceof Link) {
                if (!isset($this->resolvedLinks[$key])) {
                    $this->resolvedLinks[$key] = array_map(function ($link) {
                        return call_user_func($this->resolveLinkFunction, $link);
                    }, $this->fields[$key]);
                }

                return $this->resolvedLinks[$key];
            }
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

    /**
     * @param callable $function
     * @return self
     */
    public function setResolveLinkFunction(callable $function)
    {
        $this->resolveLinkFunction = $function;

        return $this;
    }
}
