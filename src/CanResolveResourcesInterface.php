<?php
declare(strict_types=1);

namespace Markup\Contentful;

/**
 * Interface for an object that can have a resolve function set on it.
 */
interface CanResolveResourcesInterface
{
    public function setResolveLinkFunction(callable $function);
}
