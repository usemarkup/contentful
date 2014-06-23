<?php

namespace Markup\Contentful;

/**
 * A single field defined against a content type.
 */
class ContentTypeField
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $items;

    /**
     * @var bool
     */
    private $isRequired;

    /**
     * @var bool
     */
    private $isLocalized;

    /**
     * @param string $id
     * @param string $name
     * @param string $type
     * @param array  $items
     * @param array  $options
     */
    public function __construct($id, $name, $type, array $items = [], array $options = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->items = $items;
        $defaultOptions = [
            'required' => true,
            'localized' => false,
        ];
        $options = array_merge($defaultOptions, $options);
        $this->isRequired = $options['required'];
        $this->isLocalized = $options['localized'];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return bool
     */
    public function isLocalized()
    {
        return $this->isLocalized;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->isRequired;
    }
}
