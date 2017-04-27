<?php

namespace Markup\Contentful;

/**
 * Type definition for a ResourceArray.
 */
interface ResourceArrayInterface extends \Countable, \IteratorAggregate, MetadataInterface, \ArrayAccess
{
    /**
     * Gets the total number of results in this array (i.e. not limited or offset).
     *
     * @return int
     */
    public function getTotal();

    /**
     * @return int
     */
    public function getSkip();

    /**
     * @return int
     */
    public function getLimit();

    /**
     * @return ResourceEnvelope
     */
    public function getEnvelope();

    /**
     * Gets the count of items in this array. This does not represent the total count of a result set, but the possibly offset/limited count of items in this array.
     *
     * @return int
     */
    public function count();

    /**
     * Gets the first item in this array, or null if array is empty.
     *
     * @return ResourceInterface|null
     */
    public function first();

    /**
     * Gets the last item in this array, or null if array is empty.
     *
     * @return ResourceInterface|null
     */
    public function last();
}
