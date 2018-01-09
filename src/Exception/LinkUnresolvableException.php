<?php

namespace Markup\Contentful\Exception;

use Markup\Contentful\Link;

/**
 * An exception pertaining to when a link cannot be resolved.
 */
class LinkUnresolvableException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var Link
     */
    private $link;

    /**
     * @param Link        $link
     * @param string|null $message
     * @param int         $code
     * @param \Exception  $previous
     */
    public function __construct(Link $link, $message = '', $code = 0, \Exception $previous = null)
    {
        $this->link = $link;
        parent::__construct($message ?: sprintf('The link to the %s resource with ID %s could not be resolved.', $link->getLinkType(), $link->getId()), $code, $previous);
    }

    public function getLink()
    {
        return $this->link;
    }
}
