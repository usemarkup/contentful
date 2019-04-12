<?php
declare(strict_types=1);

namespace Markup\Contentful\Log;

use Markup\Contentful\LinkInterface;

interface LinkResolveCounterInterface extends \Countable
{
    public function logLink(LinkInterface $link): void;
}
