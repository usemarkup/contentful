<?php

namespace Markup\Contentful;

use Markup\Contentful\Exception\LinkUnresolvableException;

class Entry implements EntryInterface
{
    use DisallowArrayAccessMutationTrait;
    use EntryUnknownMethodTrait;
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
        if (!isset($this->fields[$key])) {
            return null;
        }
        if (null !== $this->resolveLinkFunction) {
            if ($this->fields[$key] instanceof Link) {
                if (!isset($this->resolvedLinks[$key])) {
                    try {
                        $resolvedLink = call_user_func($this->resolveLinkFunction, $this->fields[$key])->wait();
                    } catch (LinkUnresolvableException $e) {
                        $resolvedLink = null;
                    }
                    $this->resolvedLinks[$key] = $resolvedLink;
                }

                return $this->resolvedLinks[$key];
            }
            if (is_array($this->fields[$key]) && count($this->fields[$key]) > 0 && $this->containsLink($this->fields[$key])) {
                if (!isset($this->resolvedLinks[$key])) {
                    $this->resolvedLinks[$key] = array_filter(array_map(function ($link) {
                        try {
                            $resolvedLink = call_user_func($this->resolveLinkFunction, $link)->wait();
                        } catch (LinkUnresolvableException $e) {
                            //if the link is unresolvable we should consider it not published and return null so this is filtered out
                            return null;
                        }

                        return $resolvedLink;
                    }, $this->fields[$key]));
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
     * Sets a function that can return a promise for a resolved link.
     *
     * @param callable $function
     * @return self
     */
    public function setResolveLinkFunction(callable $function)
    {
        $this->resolveLinkFunction = $function;

        return $this;
    }

    /**
     * @param array $resources
     * @return bool
     */
    private function containsLink(array $resources)
    {
        foreach ($resources as $resource) {
            if ($resource instanceof Link) {
                return true;
            }
        }

        return false;
    }
}
