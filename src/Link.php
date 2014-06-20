<?php

namespace Markup\Contentful;

class Link
{
    /**
     * The type of resource that is linked.
     *
     * @var string
     */
    private $type;

    /**
     * The ID for the linked resource.
     *
     * @var string
     */
    private $id;

    /**
     * @param string $type
     * @param string $id
     */
    public function __construct($type, $id)
    {
        $this->type = $type;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
