<?php
declare(strict_types=1);

namespace Markup\Contentful;

/**
 * An interface for links containing the accessors required to consume a link.
 */
interface LinkInterface
{
    /**
     * @var string
     */
    public function getId();

    /**
     * @var string
     */
    public function getLinkType();

    /**
     * @var string
     */
    public function getSpaceName();
}
