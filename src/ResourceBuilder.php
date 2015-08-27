<?php

namespace Markup\Contentful;

use Markup\Contentful\Decorator\AssetDecoratorInterface;
use Markup\Contentful\Decorator\NullAssetDecorator;

class ResourceBuilder
{
    /**
     * @var callable
     */
    private $resolveLinkFunction;

    /**
     * @var bool
     */
    private $useDynamicEntries;

    /**
     * @var ResourceEnvelope
     */
    private $envelope;

    /**
     * @param ResourceEnvelope|null $envelope
     */
    public function __construct(ResourceEnvelope $envelope = null)
    {
        $this->envelope = $envelope ?: new ResourceEnvelope();
    }

    /**
     * @param array                   $data      The raw data returned from the Contentful APIs.
     * @param string                  $spaceName The name being used for the space this data is from.
     * @param AssetDecoratorInterface $assetDecorator
     * @return mixed A Contentful resource.
     */
    public function buildFromData(array $data, $spaceName = null, AssetDecoratorInterface $assetDecorator = null)
    {
        $assetDecorator = $assetDecorator ?: new NullAssetDecorator();
        $buildFromData = function ($data) use ($spaceName, $assetDecorator) {
            return $this->buildFromData($data, $spaceName, $assetDecorator);
        };
        if ($this->isArrayResourceData($data)) {
            return array_map($buildFromData, $data);
        }
        $metadata = $this->buildMetadataFromSysData($data['sys'], $buildFromData);
        if (!$metadata->getType()) {
            throw new \InvalidArgumentException('Resource data must always have a type in its system properties.');
        }
        switch ($metadata->getType()) {
            case 'Space':
                $locales = [];
                $defaultLocale = null;
                foreach ($data['locales'] as $locale) {
                    $localeObj = new Locale($locale['code'], $locale['name']);
                    if (isset($locale['default']) && $locale['default']) {
                        $defaultLocale = $localeObj;
                    }
                    $locales[] = $localeObj;
                }

                return new Space($data['name'], $metadata, $locales, $defaultLocale);
            case 'Entry':
                $fields = [];
                if (isset($data['fields'])) {
                    foreach ($data['fields'] as $name => $fieldData) {
                        if ($this->isResourceData($fieldData)) {
                            $fields[$name] = $buildFromData($fieldData);
                        } elseif ($this->isArrayResourceData($fieldData)) {
                            $fields[$name] = array_map(function ($itemData) use ($buildFromData) {
                                return $buildFromData($itemData);
                            }, $fieldData);
                        } else {
                            $fields[$name] = $fieldData;
                        }
                    }
                }
                $entry = new Entry($fields, $metadata);
                if (null !== $this->resolveLinkFunction) {
                    $entry->setResolveLinkFunction($this->resolveLinkFunction);
                }
                if ($this->useDynamicEntries) {
                    $contentType = $metadata->getContentType();
                    if ($contentType instanceof Link) {
                        $contentType = call_user_func($this->resolveLinkFunction, $contentType);
                    }
                    $entry = new DynamicEntry($entry, $contentType);
                }
                $this->envelope->insertEntry($entry);

                return $entry;
            case 'Asset':
                $asset = new Asset(
                    (isset($data['fields']['title'])) ? $data['fields']['title'] : '',
                    (isset($data['fields']['description'])) ? $data['fields']['description'] : '',
                    new AssetFile(
                        (isset($data['fields']['file']) && $data['fields']['file']['fileName']) ? $data['fields']['file']['fileName'] : '',
                        (isset($data['fields']['file']) && $data['fields']['file']['contentType']) ? $data['fields']['file']['contentType'] : '',
                        (isset($data['fields']['file']) && isset($data['fields']['file']['details'])) ? $data['fields']['file']['details'] : [],
                        (isset($data['fields']['file'])) ? ((isset($data['fields']['file']['url'])) ? $data['fields']['file']['url'] : $data['fields']['file']['upload']) : ''
                    ),
                    $metadata
                );
                $asset = $assetDecorator->decorate($asset);
                $this->envelope->insertAsset($asset);

                return $asset;
            case 'ContentType':
                $buildContentTypeField = function ($fieldData) {
                    $options = [];
                    if (isset($fieldData['localized'])) {
                        $options['localized'] = $fieldData['localized'];
                    }
                    if (isset($fieldData['required'])) {
                        $options['required'] = $fieldData['required'];
                    }

                    return new ContentTypeField(
                        $fieldData['id'],
                        $fieldData['name'],
                        $fieldData['type'],
                        (isset($fieldData['items'])) ? $fieldData['items'] : [],
                        $options
                    );
                };

                $contentType = new ContentType(
                    $data['name'],
                    (isset($data['description'])) ? $data['description'] : '',
                    array_map(function ($fieldData) use ($buildContentTypeField) {
                        return $buildContentTypeField($fieldData);
                    }, (isset($data['fields'])) ? $data['fields'] : []),
                    $metadata,
                    (isset($data['displayField'])) ? $data['displayField'] : null
                );
                $this->envelope->insertContentType($contentType);

                return $contentType;
            case 'Link':
                switch ($metadata->getLinkType()) {
                    case 'Entry':
                        $entry = $this->envelope->findEntry($metadata->getId());
                        if ($entry) {
                            return $entry;
                        }
                        break;
                    case 'Asset':
                        $asset = $this->envelope->findAsset($metadata->getId());
                        if ($asset) {
                            return $asset;
                        }
                        break;
                    default:
                        break;
                }

                return new Link($metadata, $spaceName);
            case 'Array':
                $this->addToEnvelope((isset($data['includes'])) ? $data['includes'] : [], $buildFromData);

                return new ResourceArray(
                    array_map(function ($itemData) use ($buildFromData) {
                        $envelopeResource = $this->resolveResourceDataToEnvelopeResource($itemData);
                        if ($envelopeResource) {
                            return $envelopeResource;
                        }
                        $resource = $buildFromData($itemData);
                        $this->insertResourceIntoEnvelope($resource);

                        return $resource;
                    }, $data['items']),
                    intval($data['total']),
                    intval($data['limit']),
                    intval($data['skip']),
                    $this->envelope
                );
            default:
                break;
        }

        return null;
    }

    /**
     * @param callable $function
     * @return self
     */
    public function setResolveLinkFunction(callable $function)
    {
        $this->resolveLinkFunction = $function;

        return $this;
    }

    /**
     * @param bool $useDynamicEntries
     * @return self
     */
    public function setUseDynamicEntries($useDynamicEntries)
    {
        $this->useDynamicEntries = $useDynamicEntries;

        return $this;
    }

    /**
     * @param array    $sys
     * @param callable $buildFromData
     * @return Metadata
     */
    private function buildMetadataFromSysData(array $sys, callable $buildFromData)
    {
        $metadata = new Metadata();
        if (isset($sys['id'])) {
            $metadata->setId($sys['id']);
        }
        if (isset($sys['type'])) {
            $metadata->setType($sys['type']);
        }
        if (isset($sys['space'])) {
            $metadata->setSpace($buildFromData($sys['space']));
        }
        if (isset($sys['contentType'])) {
            $metadata->setContentType($buildFromData($sys['contentType']));
        }
        if (isset($sys['linkType'])) {
            $metadata->setLinkType($sys['linkType']);
        }
        if (isset($sys['revision'])) {
            $metadata->setRevision(intval($sys['revision']));
        }
        if (isset($sys['createdAt'])) {
            $metadata->setCreatedAt(new \DateTime($sys['createdAt']));
        }
        if (isset($sys['updatedAt'])) {
            $metadata->setUpdatedAt(new \DateTime($sys['updatedAt']));
        }

        return $metadata;
    }

    /**
     * @param array $data
     * @return ResourceInterface|null
     */
    private function resolveResourceDataToEnvelopeResource(array $data)
    {
        if (!isset($data['sys']['id']) || !isset($data['sys']['type'])) {
            return null;
        }
        switch ($data['sys']['type']) {
            case 'Entry':
                return $this->envelope->findEntry($data['sys']['id']);
                break;
            case 'Asset':
                return $this->envelope->findAsset($data['sys']['id']);
                break;
            case 'ContentType':
                return $this->envelope->findContentType($data['sys']['id']);
                break;
            default:
                return null;
        }
    }

    /**
     * @param ResourceInterface $resource
     */
    private function insertResourceIntoEnvelope(ResourceInterface $resource)
    {
        if ($resource instanceof EntryInterface) {
            $this->envelope->insertEntry($resource);

            return;
        }
        if ($resource instanceof ContentTypeInterface) {
            $this->envelope->insertContentType($resource);

            return;
        }
        if ($resource instanceof AssetInterface) {
            $this->envelope->insertAsset($resource);
        }
    }

    /**
     * Tests whether the provided data represents a resource, or a link to a resource.
     *
     * @param array $data
     * @return bool
     */
    private function isResourceData($data)
    {
        return is_array($data) && array_key_exists('sys', $data) && isset($data['sys']['type']);
    }

    /**
     * Tests whether the provided data represents an array of resource data, or an array of links to resources.
     *
     * @param mixed $data
     * @return bool
     */
    private function isArrayResourceData($data)
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        foreach ($data as $datum) {
            if (!$this->isResourceData($datum)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $includesData Raw data from a search response in an 'includes' node
     * @return ResourceEnvelope
     */
    private function addToEnvelope(array $includesData, callable $buildFromData)
    {
        if (isset($includesData['Entry'])) {
            foreach ($includesData['Entry'] as $entryData) {
                if (!isset($entryData['sys']['id']) || $this->envelope->hasEntry($entryData['sys']['id'])) {
                    continue;
                }
                $this->envelope->insertEntry($buildFromData($entryData));
            }
        }
        if (isset($includesData['Asset'])) {
            foreach ($includesData['Asset'] as $assetData) {
                if (!isset($assetData['sys']['id']) || $this->envelope->hasAsset($assetData['sys']['id'])) {
                    continue;
                }
                $this->envelope->insertAsset($buildFromData($assetData));
            }
        }

        return $this->envelope;
    }
}
