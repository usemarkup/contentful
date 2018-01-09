<?php

namespace Markup\Contentful\Promise;

use GuzzleHttp\Promise\PromiseInterface;
use Markup\Contentful\ResourceArray;
use Markup\Contentful\ResourceArrayInterface;
use Markup\Contentful\ResourceEnvelope;
use Markup\Contentful\ResourceInterface;

class ResourceArrayPromise extends ResourcePromise implements ResourceArrayInterface
{
    /**
     * @return \Traversable
     */
    public function getIterator()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof ResourceArrayInterface) {
            return new \ArrayIterator();
        }

        return $resolved->getIterator();
    }

    /**
     * Gets the total number of results in this array (i.e. not limited or offset).
     *
     * @return int
     */
    public function getTotal()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof ResourceArrayInterface) {
            return 0;
        }

        return $resolved->getTotal();
    }

    /**
     * @return int
     */
    public function getSkip()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof ResourceArrayInterface) {
            return 0;
        }

        return $resolved->getSkip();
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof ResourceArrayInterface) {
            return 0;
        }

        return $resolved->getLimit();
    }

    /**
     * @return ResourceEnvelope
     */
    public function getEnvelope()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof ResourceArrayInterface) {
            return new ResourceEnvelope();
        }

        return $resolved->getEnvelope();
    }

    /**
     * Gets the count of items in this array. This does not represent the total count of a result set, but the possibly offset/limited count of items in this array.
     *
     * @return int
     */
    public function count()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof ResourceArrayInterface) {
            return 0;
        }

        return count($resolved);
    }

    /**
     * Gets the first item in this array, or null if array is empty.
     *
     * @return ResourceInterface|null
     */
    public function first()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof ResourceArrayInterface) {
            return null;
        }

        return $resolved->first();
    }

    /**
     * Gets the last item in this array, or null if array is empty.
     *
     * @return ResourceInterface|null
     */
    public function last()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof ResourceArrayInterface) {
            return null;
        }

        return $resolved->last();
    }

    /**
     * @param PromiseInterface $promise
     * @return PromiseInterface
     */
    protected function addRejectionHandlerToPromise(PromiseInterface $promise)
    {
        return $promise
            ->otherwise(function ($reason) {
                return new ResourceArray([], 0, 0, 0);
            });
    }

    protected function doResolve(PromiseInterface $promise)
    {
        $resolved = $promise->wait();
        //temporarily set resolved resource with array that may contain nulls
        $this->setResolvedResource($resolved);
        //now set it again but with access to skip/limit parameters etc - using a resource array will auto-filter nulls
        $this->setResolvedResource(new ResourceArray(
            $resolved,
            $this->getTotal(),
            $this->getSkip(),
            $this->getLimit(),
            $this->getEnvelope()
        ));
    }
}
