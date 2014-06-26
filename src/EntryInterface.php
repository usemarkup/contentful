<?php

namespace Markup\Contentful;

interface EntryInterface extends ResourceInterface, \ArrayAccess
{
    /**
     * Gets the list of field values in the entry, keyed by fields. Could be scalars, DateTime objects, or links.
     *
     * @return array
     */
    public function getFields();

    /**
     * Gets an individual field value, or null if the field is not defined.
     *
     * @return mixed
     */
    public function getField($key);
} 
