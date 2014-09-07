<?php

namespace Markup\Contentful;

class ResourceArray implements \Countable, \IteratorAggregate, MetadataInterface, \ArrayAccess
{
    use MetadataAccessTrait;

    /**
     * @var array
     */
    private $items;

    /**
     * @var int
     */
    private $total;

    /**
     * @var int
     */
    private $skip;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var ResourceEnvelope
     */
    private $envelope;

    /**
     * @param  $items
     * @param int $total
     * @param int $skip
     * @param int $limit
     * @param ResourceEnvelope $envelope
     */
    public function __construct($items, $total, $skip, $limit, ResourceEnvelope $envelope = null)
    {
        if ($items instanceof \Traversable) {
            $this->items = array_values(iterator_to_array($items));
        } elseif (is_array($items)) {
            $this->items = array_values($items);
        } else {
            throw new \InvalidArgumentException('Items parameter should be an array or a traversable object.');
        }
        $this->total = $total;
        $this->skip = $skip;
        $this->limit = $limit;
        $this->envelope = $envelope ?: new ResourceEnvelope();
    }

    /**
     * Gets the total number of results in this array (i.e. not limited or offset).
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getSkip()
    {
        return $this->skip;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return \Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @return ResourceEnvelope
     */
    public function getEnvelope()
    {
        return $this->envelope;
    }

    /**
     * Gets the count of items in this array. This does not represent the total count of a result set, but the possibly offset/limited count of items in this array.
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param mixed $offset
     * @return ResourceInterface|null
     */
    public function offsetGet($offset)
    {
        if (!isset($this->items[$offset])) {
            return null;
        }

        return $this->items[$offset];
    }

    /**
     * @param mixed $offset <p>
     * @param mixed $value  <p>
     * @return void
     * @throws \BadMethodCallException
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException();
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException();
    }
}
