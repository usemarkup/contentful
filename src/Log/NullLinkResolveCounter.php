<?php
declare(strict_types=1);

namespace Markup\Contentful\Log;

use Markup\Contentful\LinkInterface;

class NullLinkResolveCounter implements LinkResolveCounterInterface
{
    public function count(): int
    {
        return 0;
    }

    public function logLink(LinkInterface $link): void
    {
        //do nothing
    }
}
