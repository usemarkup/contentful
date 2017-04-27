<?php

namespace Markup\Contentful\Promise;

use GuzzleHttp\Promise\PromiseInterface;
use Markup\Contentful\Entry;
use Markup\Contentful\EntryInterface;
use Markup\Contentful\Metadata;

/**
 * An entry implementation that is also a promise, auto-resolving when field access is attempted.
 */
class EntryPromise extends ResourcePromise implements EntryInterface
{
    /**
     * Gets the list of field values in the entry, keyed by fields. Could be scalars, DateTime objects, or links/resources.
     *
     * @return array
     */
    public function getFields()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof EntryInterface) {
            return [];
        }

        return $resolved->getFields();
    }

    /**
     * Gets an individual field value, or null if the field is not defined.
     *
     * @return mixed
     */
    public function getField($key)
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof EntryInterface) {
            return null;
        }

        return $resolved->getField($key);
    }

    /**
     * @param PromiseInterface $promise
     * @return PromiseInterface
     */
    protected function addRejectionHandlerToPromise(PromiseInterface $promise)
    {
        return $promise
            ->otherwise(function ($reason) {
                return new Entry([], new Metadata());
            });
    }
}
