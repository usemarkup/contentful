<?php
declare(strict_types=1);

namespace Markup\Contentful\Log;

use Markup\Contentful\LinkInterface;

class LinkResolveCounter implements LinkResolveCounterInterface
{
    /**
     * @var array
     */
    private $loggedLinks;

    public function __construct()
    {
        $this->loggedLinks = [];
    }

    public function count(): int
    {
        return count($this->loggedLinks);
    }

    public function logLink(LinkInterface $link): void
    {
        $key = $this->calculateLinkKey($link);
        if (!in_array($key, $this->loggedLinks)) {
            $this->loggedLinks[] = $key;
        }
    }

    private function calculateLinkKey(LinkInterface $link): string
    {
        return sprintf('%s:%s', $link->getId(), $link->getSpaceName());
    }
}
