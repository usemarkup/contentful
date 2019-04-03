<?php
declare(strict_types=1);

namespace Markup\Contentful;

trait ResourceInsertDelegationTrait
{
    /**
     * @param ResourceInterface|ResourceArray $resource
     * @return $this
     */
    public function insert($resource): ResourceEnvelopeInterface
    {
        if ($resource instanceof ResourceArray) {
            foreach ($resource as $resourceItem) {
                $this->insert($resourceItem);
            }
        }
        if ($resource instanceof EntryInterface) {
            return $this->insertEntry($resource);
        }
        if ($resource instanceof AssetInterface) {
            return $this->insertAsset($resource);
        }
        if ($resource instanceof ContentTypeInterface) {
            return $this->insertContentType($resource);
        }

        return $this;
    }
}
