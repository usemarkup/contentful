<?php
declare(strict_types=1);

namespace Markup\Contentful;

/**
 * A pool of resource envelopes.
 */
class ResourceEnvelopePool
{
    /**
     * @var ResourceEnvelopeInterface[]
     */
    private $envelopes;

    public function __construct()
    {
        $this->envelopes = [];
    }

    public function getEnvelopeForSpace(string $space): ResourceEnvelopeInterface
    {
        if (!array_key_exists($space, $this->envelopes)) {
            throw new \RuntimeException(sprintf('No resource envelope exists for the space "%s".', $space));
        }

        return $this->envelopes[$space];
    }

    public function registerEnvelopeForSpace(ResourceEnvelopeInterface $envelope, string $space)
    {
        $this->envelopes[$space] = $envelope;
    }
}
