<?php

namespace Markup\Contentful;

class ResourceArray implements \Countable, \IteratorAggregate, MetadataInterface
{
    use MetadataAccessTrait;

    /**
     * @var \Iterator
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
     * @param  $items
     * @param int $total
     * @param int $skip
     * @param int $limit
     */
    public function __construct($items, $total, $skip, $limit)
    {
        if ($items instanceof \Iterator) {
            $this->items = $items;
        } elseif ($items instanceof \Traversable) {
            $this->items = new \IteratorIterator($items);
        } elseif (is_array($items)) {
            $this->items = new \ArrayIterator($items);
        } else {
            throw new \InvalidArgumentException('Items parameter should be an array or a traversable object.');
        }
        $this->total = $total;
        $this->skip = $skip;
        $this->limit = $limit;
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
        return $this->items;
    }

    /**
     * Gets the count of items in this array. This does not represent the total count of a result set, but the possibly offset/limited count of items in this array.
     *
     * @return int
     */
    public function count()
    {
        if (!$this->items instanceof \Countable) {
            return count(iterator_to_array($this->items));
        }

        return count($this->items);
    }
}
