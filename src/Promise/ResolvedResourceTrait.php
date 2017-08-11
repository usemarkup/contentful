<?php

namespace Markup\Contentful\Promise;

use function GuzzleHttp\Promise\is_fulfilled;
use GuzzleHttp\Promise\PromiseInterface;
use Markup\Contentful\ResourceArray;
use Markup\Contentful\ResourceInterface;

trait ResolvedResourceTrait
{
    /**
     * ResourceInterface|ResourceArray|null
     */
    private $resolvedResource;

    abstract protected function getPromise();

    private function ensureResolved()
    {
        $promise = $this->getPromise();
        if (is_fulfilled($promise) && null !== $this->resolvedResource) {
            return;
        }
        $this->doResolve($promise);
    }

    protected function doResolve(PromiseInterface $promise)
    {
        $this->setResolvedResource($promise->wait());
    }

    protected function setResolvedResource($resource)
    {
        $this->resolvedResource = $resource;
    }

    /**
     * @return ResourceInterface|ResourceArray
     */
    protected function getResolved()
    {
        $this->ensureResolved();

        return $this->resolvedResource;
    }
}
