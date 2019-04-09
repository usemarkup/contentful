<?php
declare(strict_types=1);

namespace Markup\Contentful;

/**
 * An interface for links containing the accessors required to consume a link.
 */
interface LinkInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getLinkType();

    /**
     * @return string
     */
    public function getSpaceName();
}
