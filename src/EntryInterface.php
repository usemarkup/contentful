<?php

namespace Markup\Contentful;

interface EntryInterface extends ResourceInterface
{
    /**
     * Gets the list of field values in the entry, keyed by fields. Could be scalars, DateTime objects, or links.
     *
     * @return array
     */
    public function getFields();
} 
