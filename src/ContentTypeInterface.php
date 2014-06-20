<?php

namespace Markup\Contentful;

interface ContentTypeInterface extends ResourceInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return ContentTypeField[]
     */
    public function getFields();

    /**
     * @return ContentTypeField|null
     */
    public function getDisplayField();
} 
